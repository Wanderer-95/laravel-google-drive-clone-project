export function httpGet(uri) {
    return fetch(uri, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then((response) => response.json());
}

export async function httpRequest(
    url,
    method,
    data,
    headers
) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const combinedHeaders = {
    'Content-Type': 'application/json',
    ...headers,
};

if (csrfToken) {
    (combinedHeaders)['X-CSRF-TOKEN'] = csrfToken;
}

const body = data ? JSON.stringify(data) : undefined;

const response = await fetch(url, {
    method,
    headers: combinedHeaders,
    body,
});

const responseText = await response.text();
if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
}

try {
    return JSON.parse(responseText);
} catch (e) {
    throw e;
}
}
