const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type'
};

exports.handler = async function (event) {
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 204, headers: CORS_HEADERS, body: '' };
  }

  const apiUrl = process.env.SKINRAVE_API_URL; // full URL expected in env
  if (!apiUrl) {
    return { statusCode: 500, headers: CORS_HEADERS, body: JSON.stringify({ error: 'Missing SKINRAVE_API_URL environment variable' }) };
  }

  try {
    const res = await fetch(apiUrl, { method: 'GET', headers: { accept: 'application/json' } });
    if (!res.ok) {
      return { statusCode: res.status, headers: CORS_HEADERS, body: JSON.stringify({ error: 'Upstream API returned status ' + res.status }) };
    }

    const payload = await res.json();
    const totalCount = payload && payload.totalCount ? Number(payload.totalCount) : 0;
    const filteredCount = payload && payload.filteredCount ? Number(payload.filteredCount) : 0;
    const list = payload && Array.isArray(payload.list) ? payload.list : (Array.isArray(payload) ? payload : []);

    return { statusCode: 200, headers: Object.assign({}, CORS_HEADERS, { 'Content-Type': 'application/json' }), body: JSON.stringify({ totalCount, filteredCount, list }) };
  } catch (err) {
    return { statusCode: 500, headers: CORS_HEADERS, body: JSON.stringify({ error: err.message }) };
  }
};