export function httpGet(url) {
    return fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        }
    }).then(response => {
        return response.json();
    });
}

export function httpPost(url, data) {
    return new Promise((resolve, reject) => {
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': getCookie('XSRF-TOKEN'),
            },
            body: JSON.stringify(data),
        }).then(response => {
            if (response.ok) {
                resolve(response.json());
            } else {
                response.json().then(data => {
                    reject({error: data});
                })
            }
        });
    });
}


function getCookie(cookieName) {
    let name = cookieName + '=';

    let decodedCookie = decodeURIComponent(document.cookie);
    let split = decodedCookie.split(';');

    for (let i = 0; i < split.length; i++) {
        let key = split[i];
        while (key.charAt(0) == ' ') {
            key = key.substring(1);
        }

        if (key.indexOf(name) == 0) {
            return key.substring(name.length, key.length);
        }
    }

    return "";
}
