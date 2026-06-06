const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type'
};

function detectRaceEndIso(race) {
  const candidates = ['ends_at', 'end_at', 'end_time', 'endTime', 'end', 'ends_at_timestamp', 'end_timestamp', 'expires_at', 'expires_at_timestamp', 'scheduled_end'];
  for (const key of candidates) {
    if (race && Object.prototype.hasOwnProperty.call(race, key) && race[key]) {
      const val = race[key];
      if (typeof val === 'number') return new Date(val * 1000).toISOString();
      return String(val);
    }
  }
  return '';
}

exports.handler = async function (event) {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 204, headers: CORS_HEADERS, body: '' };
  }

  const RAIN_API_KEY = process.env.RAIN_API_KEY;
  if (!RAIN_API_KEY) {
    return { statusCode: 500, headers: CORS_HEADERS, body: JSON.stringify({ error: 'Missing RAIN_API_KEY environment variable' }) };
  }

  const apiUrl = 'https://api.rain.gg/v1/affiliates/races?participant_count=10';

  try {
    const res = await fetch(apiUrl, {
      method: 'GET',
      headers: {
        accept: 'application/json',
        'x-api-key': RAIN_API_KEY
      }
    });

    if (!res.ok) {
      return { statusCode: res.status, headers: CORS_HEADERS, body: JSON.stringify({ error: 'Upstream API returned status ' + res.status }) };
    }

    const data = await res.json();
    let entries = [];
    let endsAt = '';
    if (Array.isArray(data.results) && data.results.length > 0) {
      const race = data.results[0];
      entries = Array.isArray(race.participants) ? race.participants.slice(0, 10) : [];
      endsAt = detectRaceEndIso(race);
    }

    return { statusCode: 200, headers: Object.assign({}, CORS_HEADERS, { 'Content-Type': 'application/json' }), body: JSON.stringify({ entries, endsAt }) };
  } catch (err) {
    return { statusCode: 500, headers: CORS_HEADERS, body: JSON.stringify({ error: err.message }) };
  }
};