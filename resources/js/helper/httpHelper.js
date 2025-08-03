export function httpGet(uri) {
    return fetch(uri, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then((response) => response.json());
}
