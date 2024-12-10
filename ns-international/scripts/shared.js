const API_URL = "https://nsi-api.goedkoop-treinkaartje.nl/api";
// const API_URL = "https://localhost:7096/api";

async function searchStations(name) {
  if (name.length < 3) {
    return [];
  }

  const url = `${API_URL}/stations/${name}`;

  const response = await fetch(url);
  const data = await response.json();

  return data;
}

async function getStationByCode(code) {
  const url = `${API_URL}/stations/bene/${code}`;

  const response = await fetch(url);
  const data = await response.json();
  if (!data) {
    return "";
  }

  return data.name;
}

async function loadCalendar(from, to, date) {
  const url = `${API_URL}/calendar/${from}/${to}`;

  const response = await fetch(url);
  const data = await response.json();

  return data;
}

async function loadDayschedule(from, to, date) {
  const url = `${API_URL}/dayschedule/${from}/${to}/${date}`;

  const response = await fetch(url);
  const data = await response.json();

  return data;
}

async function searchSchedule(from, to, date) {
  const url = `${API_URL}/search/${from}/${to}/${date}`;

  const response = await fetch(url);
  const sessionId = response.headers.get("X-Session-Id");
  const fromCache = response.headers.get("x-from-cache");

  const data = await response.json();

  let scrollObj = null;
  if (data.data.scroll.later === "NEXT_DEPARTURE") {
    scrollObj = {
      id: data.data.scroll.searchId,
      token: response.headers.get("x-nsiapi-convid"),
    };
  } else if (fromCache !== "1") {
    searchScheduleSetCache(from, to, date, data, response.headers);
  }

  return {
    data: data.data.travelOffers,
    sessionId: sessionId,
    scroll: scrollObj,
    fromCache: fromCache === "1",
  };
}

async function searchScheduleSetCache(from, to, date, response, headers) {
  const url = `${API_URL}/set-cache/search/${from}/${to}/${date}`;

  const headerList = Array.from(headers).map((x) => ({
    key: x[0],
    value: x[1],
  }));

  fetch(url, {
    method: "POST",
    body: JSON.stringify({
      duration: 60,
      content: JSON.stringify(response),
      headers: headerList,
    }),
  });
}

async function loadSearchScroll(id, headerToken, sessionId, from, to, date) {
  const url = `${API_URL}/SearchScroll/${id}/${headerToken}`;

  const response = await fetch(url, {
    headers: {
      "X-Session-Id": sessionId,
    },
  });
  const data = await response.json();

  let scrollObj = null;
  if (data.data.scroll.later === "NEXT_DEPARTURE") {
    scrollObj = {
      id: data.data.scroll.searchId,
      token: response.headers.get("x-nsiapi-convid"),
    };
  } else {
    searchScheduleSetCache(from, to, date, data, response.headers);
  }

  return {
    data: data.data.travelOffers,
    sessionId: sessionId,
    scroll: scrollObj,
  };
}

const slash = "%2F";
function getBaseTrackingUrl() {
  return `https://www.nsinternational.com/traintracker/?tt=${php_vars.tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F`;
}

function getTrackingUrl(from, to, date, departure, arival) {
  let path = `${from}${slash}${to}`;

  if (date) {
    path += `${slash}${date}`;
  }

  if (departure) {
    path += `${slash}${departure}`;
  }

  if (arival) {
    path += `${slash}${arival}`;
  }

  return getBaseTrackingUrl() + path;
}

function toPrice(price) {
  return price.toLocaleString("nl-NL", {
    style: "currency",
    currency: "EUR",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  });
}

function toFullPrice(price) {
  const [whole, fraction] = price
    .toLocaleString("nl-NL", {
      style: "currency",
      currency: "EUR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 2,
    })
    .split(",");

  if (!fraction) {
    return whole;
  }

  return `${whole}<span class="fraction">,${fraction.padEnd(2, "0")}</span>`;
}
