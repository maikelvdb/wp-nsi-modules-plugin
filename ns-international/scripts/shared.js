const API_URL = "https://nsi-api.goedkoop-treinkaartje.nl/api";
//const API_URL = "https://nsi.maikelvdb.nl/api";

async function searchStations(name) {
  if (name.length < 3) {
    return [];
  }

  const url = `${API_URL}/stations/${name}`;

  try {
    const response = await fetchData(url);
    const data = await response.json();

    return data;
  } catch {
    return null;
  }
}

async function getStationByCode(code) {
  const url = `${API_URL}/stations/bene/${code}`;

  const response = await fetchData(url);
  const data = await response.json();
  if (!data) {
    return "";
  }

  return data.name;
}

async function loadCalendar(from, to, date) {
  const url = `${API_URL}/calendar/${from}/${to}`;

  const response = await fetchData(url);
  const data = await response.json();

  return data;
}

async function loadDayschedule(from, to, date) {
  const url = `${API_URL}/dayschedule/${from}/${to}/${date}`;

  try {
    const response = await fetchData(url);
    const data = await response.json();

    return data;
  } catch {
    return null;
  }
}

async function searchSchedule(from, to, date) {
  const url = `${API_URL}/search/${from}/${to}/${date}`;

  try {
    const response = await fetchData(url);
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
  } catch {
    return null;
  }
}

async function searchScheduleSetCache(from, to, date, response, headers) {
  const url = `${API_URL}/set-cache/search/${from}/${to}/${date}`;

  const headerList = Array.from(headers).map((x) => ({
    key: x[0],
    value: x[1],
  }));

  fetchData(url, {
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

  const response = await fetchData(url, {
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

async function streamSearchData(
  from,
  to,
  date,
  $container,
  renderDataCallback
) {
  try {
    const response = await fetch(`${API_URL}/Search/${from}/${to}/${date}`);

    if (!response.ok || !response.body) {
      throw new Error(
        "Network response was not ok or streaming not supported."
      );
    }

    const reader = response.body.getReader();
    const decoder = new TextDecoder("utf-8");
    let buffer = "";

    while (true) {
      const { done, value } = await reader.read();
      if (done) {
        break;
      }

      buffer += decoder.decode(value, { stream: true });
      let parts = buffer.split("\n");
      buffer = parts.pop();

      for (const part of parts) {
        if (part.trim() === "") continue;
        try {
          const jsonArray = JSON.parse(part);
          renderDataCallback(date, $container, jsonArray, false);
        } catch (err) {
          throw new Error("Error parsing JSON:", err, part);
        }
      }
    }

    if (buffer.trim()) {
      try {
        const jsonArray = JSON.parse(buffer);
        renderDataCallback(date, $container, jsonArray, true);
      } catch (err) {
        throw new Error("Error parsing final JSON:", err, buffer);
      }
    }
  } catch (error) {
    throw new Error("Error reading stream:", error);
  }
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

async function fetchData(url, options) {
  return await fetch(url, options ?? null)
    .then((response) => response)
    .catch(() => null);
}
