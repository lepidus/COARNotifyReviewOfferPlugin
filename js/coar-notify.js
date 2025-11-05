function sendNotificationHandler(
    sendNotificationUrl,
    originInboxUrl,
    originHomeUrl,
    targetInboxUrl,
    targetHomeUrl,
    actorId,
    actorName,
    doi = "10.5555/12345680",
    sendButtonId,
) {
    const button = document.getElementById(sendButtonId);
    button.textContent = 'Sending...';
    button.disabled = true;

    const payload = {
        "id": "urn:uuid:" + generateUUID(),
        "@context": [
            "https://www.w3.org/ns/activitystreams",
            "https://purl.org/coar/notify"
        ],
        "actor": {
            "id": actorId,
            "name": actorName,
            "type": "Person"
        },
        "object": {
            "id": doi,
            "ietf:cite-as": "https://doi.org/" + doi,
        },
        "origin": {
            "id": originHomeUrl,
            "inbox": originInboxUrl,
            "type": "Service"
        },
        "target": {
            "id": targetHomeUrl,
            "inbox": targetInboxUrl,
            "type": "Service"
        },
        "type": [
            "Offer",
            "coar-notify:ReviewAction"
        ]
    };

    fetch(sendNotificationUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            targetInboxUrl: targetInboxUrl,
            payload: payload
        }),
    })
        .then(response => response)
        .then(data => {
            console.log('Success:', data);
            button.replaceWith('Sent');
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Failed to send the notification.');
            button.textContent = 'Retry';
            button.disabled = false;
        });

}


function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = Math.random() * 16 | 0,
            v = c === 'x' ? r : (r & 0x3 | 0x8);
        return v.toString(16);
    });
}

function createActorData() {}

function createOriginData() {}


