// The options variable gets created using wp_localize_script in tokopa-registration.php
getOrders(options.deposit_id, "deposit", getBalanceInfo);
getOrders(options.product_id, "full");

const wrapper = document.querySelector(".registration-wrapper");

// Display generating message
if (getUrlVars().reminders) {
  const msg = document.createElement("p");
  msg.classList.add("large");
  msg.innerHTML = "Generating orders...";
  wrapper.parentNode.insertBefore(msg, wrapper.nextSibling);
} else {
  wrapper.style.display = 'block';
}

async function getBalanceInfo() {
  const balance_data = await getData(options.plugin_url + "/order-balance.php");
  const balance_variations = await getData(`/wp-json/wc/v3/products/${options.balance_id}/variations`);

  let balance_amounts = [];
  balance_variations.map(variation => {
    balance_amounts.push({
      variation_id: variation.id,
      variation: variation.attributes[0].option,
      amount: variation.price
    });
  });

  balance_data.map(balance => {
    const row = document.querySelector(`tr[data-orderid="${balance.deposit_order_id}"]`);
    const order = JSON.parse(row.dataset.order);

    if (!balance.balance_order_id) {
      const variation = balance_amounts.filter(
        amount => amount.variation === row.dataset.accommodation
      )[0];

      const balance_order = {
        "customer_id": order.customer_id,
        "billing": order.billing,
        "shipping": order.shipping,
        "line_items": [
          {
            "product_id": options.balance_id,
            "variation_id": variation.variation_id,
            "quantity": 1
          }
        ]
      };

      // Create the Balance Order
      createBalanceOrder(balance_order).then((response) => {

        // Update the Registration Table
        updateRegistrationOrder({
          deposit_order_id: order.id,
          balance_order_id: response.id,
          reminder_sent: null
        }).then(response => {
          balance.balance_order_id = response.balance_order_id;
          displayBalanceDetails(balance);
        });
      });
    } else {
      displayBalanceDetails(balance);
    }

    // Redirect if coming from the payment reminders page
    if (getUrlVars().reminders) {
      window.location = '/wp-admin/admin.php?page=tokopa-registration/payment-reminders.php';
    }

  });
}

async function displayBalanceDetails({ deposit_order_id, balance_order_id, reminder_sent }) {
  const row = document.querySelector(`tr[data-orderid="${deposit_order_id}"]`);
  const balance_order_column = row.querySelector(".balance-order");
  const balance_amount_column = row.querySelector(".balance-amount");
  const balance_email_column = row.querySelector(".balance-email");

  const balance_order_details = await getData(`/wp-json/wc/v3/orders/${balance_order_id}`);

  balance_order_column.append(createLink(`/wp-admin/post.php?post=${balance_order_id}&action=edit`, balance_order_id));
  balance_amount_column.innerHTML = "$" + balance_order_details.total;

  if (reminder_sent === '0000-00-00 00:00:00') {
    balance_email_column.innerHTML = "Not Sent";
  } else {
    reminder_date = new Date(reminder_sent);
    balance_email_column.innerHTML = displayDate(reminder_date);
  }
}

async function getOrders(id, target, callback) {
  const orders = await getData(`/wp-json/wc/v3/orders?product=${id}`)
  displayOrders(orders, target);
  if (callback) callback();
}

function getData(url) {
  return new Promise(resolve => {
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = true;

    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === 4) {
        const data = JSON.parse(this.responseText);
        resolve(data);
      }
    });

    xhr.open("GET", url);
    xhr.setRequestHeader("Authorization", "Basic " + options.key);
    xhr.setRequestHeader("Accept", "*/*");
    xhr.setRequestHeader("Cache-Control", "no-cache");
    xhr.setRequestHeader("cache-control", "no-cache");

    xhr.send();
  });
}

function displayOrders(data, type) {
  let target;
  if (type === "deposit") {
    target = document.querySelector("#paid_deposit tbody");
  } else {
    target = document.querySelector("#paid_in_full tbody");
  }

  if (data.length === 0) {
    target.querySelector(".loading").innerHTML = "No orders found.";
    return
  }

  target.innerHTML = '';

  data.map(order => {
    const tr = document.createElement("tr");

    const order_id = document.createElement("td");
    const date = document.createElement("td");
    const status = document.createElement("td");
    const order_id_link = document.createElement("a");
    const name = document.createElement("td");
    const email = document.createElement("td");
    const email_link = document.createElement("a");
    const accommodation = document.createElement("td");
    const amount = document.createElement("td");

    order_id.append(createLink(`/wp-admin/post.php?post=${order.id}&action=edit`, order.id));

    const formatted_date = new Date(order.date_created);
    date.innerHTML = displayDate(formatted_date);

    status.innerHTML = order.status;

    name.innerHTML = order.billing.first_name + ' ' + order.billing.last_name;

    email_link.href = `mailto:${order.billing.email}`;
    email_link.innerHTML = order.billing.email;
    email.append(email_link);

    accommodation.innerHTML = order.line_items[0].meta_data[0].value;

    amount.innerHTML = "$" + order.line_items[0].total;

    tr.setAttribute("data-orderid", order.id);
    tr.setAttribute("data-accommodation", order.line_items[0].meta_data[0].value);
    tr.setAttribute("data-order", JSON.stringify(order));
    tr.classList.add(type + "-row");

    tr.append(order_id);
    tr.append(date);
    tr.append(status);
    tr.append(name);
    tr.append(email);
    tr.append(amount);
    tr.append(accommodation);

    if (type === "deposit") {
      const balance_order = document.createElement("td");
      const balance_amount = document.createElement("td");
      const balance_email = document.createElement("td");

      balance_order.classList.add("balance-order");
      balance_amount.classList.add("balance-amount");
      balance_email.classList.add("balance-email");

      tr.append(balance_order);
      tr.append(balance_amount);
      tr.append(balance_email);
    }

    target.append(tr);
  });
}

function displayDate(date) {
  return date.getMonth() + 1 + '/' + date.getDate() + '/' + date.getFullYear();
}

function createBalanceOrder(order) {
  const order_str = JSON.stringify(order);

  return new Promise(resolve => {
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = true;

    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === 4) {
        const data = JSON.parse(this.responseText);
        resolve(data);
      }
    });

    xhr.open("POST", '/wp-json/wc/v3/orders');
    xhr.setRequestHeader("Authorization", "Basic " + options.key);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    xhr.send(order_str);
  });
}

function updateRegistrationOrder(data) {
  return new Promise(resolve => {
    var xhr = new XMLHttpRequest();
    xhr.withCredentials = true;

    xhr.addEventListener("readystatechange", function () {
      if (this.readyState === 4) {
        const data = JSON.parse(this.responseText);
        resolve(data);
      }
    });

    xhr.open("POST", options.plugin_url + "/registration-order-update.php");
    xhr.setRequestHeader("Authorization", "Basic " + options.key);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");

    xhr.send(JSON.stringify(data));
  });
}

function createLink(url, text) {
  const a = document.createElement("a");
  a.href = url;
  a.innerHTML = text;
  return a;
}

function getUrlVars() {
  var vars = {};
  var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
    vars[key] = value;
  });
  return vars;
}