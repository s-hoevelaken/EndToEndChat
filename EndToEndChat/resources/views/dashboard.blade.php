@extends('layouts.appTrad')
@section('content')

<div class=" flex flex-col main-content bg-gray-800">
    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Friends List -->
        <div class="w-1/4 bg-gray-700 overflow-y-auto">
    <h2 class="text-white text-lg p-5">Friends</h2>
    <ul id="friendsList" class="divide-y divide-gray-600">
    <!-- Dynamic items will be inserted here -->
    </ul>
</div>
        
        <!-- Chat Area -->
        <div class="flex-1 p-10 overflow-y-auto">
            <div class="flex flex-col justify-between h-full">
                <!-- Chat Messages -->
                <div class="chat-messages overflow-y-auto mb-4">
                    <!-- Dynamic Chat Messages Here -->
                </div>
                <!-- Message Input -->
                <div class="mt-4 flex">
                    <input type="text" id="messageInput" class="w-full p-2 rounded-lg" placeholder="Type a message...">
                    <button id="sendButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Send</button>
                </div>
            </div>
        </div>
</div>
@endsection
<style>
    .main-content 
    {
        height: calc(100vh - 65px) !important;
        overflow-y: hidden;
    }
</style>

<script>
const authUserId = @json(auth()->id());
let selectedFriendId = null;
    async function loadFriends() {
        try {
            const response = await fetch('/api/friends', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();
            const list = document.getElementById('friendsList');
            list.innerHTML = '';
            data.forEach(friend => {
                const li = document.createElement('li');
                li.classList.add('py-2', 'px-4', 'hover:bg-gray-600', 'cursor-pointer', 'text-white');
                li.textContent = friend.name;
                li.addEventListener('click', () => {
                    openChat(friend.id);
                });
                list.appendChild(li);
            });
        } catch (error) {
            console.error('Error loading friends:', error);
        }
    }  
    
    

async function openChat(friendId) {
    selectedFriendId = friendId;
    try {
        const response = await fetch(`/api/chat/${friendId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        const result = await response.json();
        if (!result.data) {
            console.error('No messages found or end of message list.');
            return; // Exit if there are no messages
        }

        const messages = result.data;
        console.log(messages);
        const privateKey = await getPrivateKey();

        const messagesContainer = document.querySelector('.chat-messages');
        messagesContainer.innerHTML = '';
        let mess = 0;
        for (const message of messages) {
            mess += 1;
            if (!message.iv || !message.content) {
                console.error("Message IV or content missing:", message);
                continue; // Skip processing the message if iv or content is missing
            }

            let symmKeyEnc = message.sender_id === authUserId ? message.sender_symm_key_enc : message.recipient_symm_key_enc;
            let decryptedSymmKey = await decryptSymmetricKey(symmKeyEnc, privateKey);
            let decryptedMessage = await decryptMessage(message.content, decryptedSymmKey, message.iv);

            console.log(mess);
            console.log(decryptedMessage);

            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            messageElement.textContent = decryptedMessage;
            messageElement.className += message.sender_id === authUserId ? ' sent' : ' received';
            messagesContainer.appendChild(messageElement);
        }
    } catch (error) {
        console.error('Error opening chat:', error);
    }
}




async function getPublicKeyById(userId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    try {
        const response = await fetch(`/api/user/public-key/${userId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        return data.public_key;
    } catch (error) {
        console.error('Failed to fetch public key:', error);
        return null;
    }
}

async function generateSymmetricKey() {
    return await window.crypto.subtle.generateKey(
        {
            name: "AES-GCM",
            length: 128,
        },
        true,
        ["encrypt", "decrypt"]
    );
}

async function encryptMessage(message, symmetricKey) {
    const encoder = new TextEncoder();
    const encodedMessage = encoder.encode(message);
    const iv = window.crypto.getRandomValues(new Uint8Array(12)); // IV because of AES

    const encryptedContent = await window.crypto.subtle.encrypt(
        {
            name: "AES-GCM",
            iv: iv,
        },
        symmetricKey,
        encodedMessage
    );

    return {
        encryptedMessage: window.btoa(String.fromCharCode(...new Uint8Array(encryptedContent))),
        iv: window.btoa(String.fromCharCode(...new Uint8Array(iv)))
    };
}

async function decryptSymmetricKey(encryptedKey, privateKey) {
    const encryptedKeyArrayBuffer = new Uint8Array(atob(encryptedKey).split("").map(char => char.charCodeAt(0)));
    const decryptedKey = await window.crypto.subtle.decrypt(
        { name: "RSA-OAEP" },
        privateKey,
        encryptedKeyArrayBuffer
    );
    return await window.crypto.subtle.importKey(
        "raw",
        decryptedKey,
        { name: "AES-GCM" },
        false,
        ["decrypt"]
    );
}


async function getPrivateKey() {
    try {
        const keyData = await retrievePrivateKeyFromIndexedDB();
        if (!keyData.value) throw new Error("Private key not found in database.");

        const privateKey = await window.crypto.subtle.importKey(
            "jwk", 
            keyData.value, 
            {
                name: "RSA-OAEP",
                hash: {name: "SHA-256"}
            },
            false, 
            ["decrypt"] 
        );
        return privateKey;
    } catch (error) {
        console.error('Failed to get or import private key:', error);
        throw error;
    }
}

async function retrievePrivateKeyFromIndexedDB() {
    return new Promise((resolve, reject) => {
        const dbRequest = indexedDB.open('CryptoDB', 1);

        dbRequest.onerror = function(event) {
            console.error('Database error:', event.target.error);
            reject(event.target.error);
        };

        dbRequest.onsuccess = function(event) {
            const db = event.target.result;
            const transaction = db.transaction('Keys', 'readonly');
            const store = transaction.objectStore('Keys');
            const getRequest = store.get('PrivateKey');

            getRequest.onsuccess = function() {
                if (getRequest.result) {
                    resolve(getRequest.result);
                } else {
                    reject(new Error('Private key not found.'));
                }
            };

            getRequest.onerror = function(event) {
                console.error('Error retrieving private key:', event.target.error);
                reject(event.target.error);
            };
        };
    });
}



async function decryptMessage(encryptedData, symmKey, iv) {
    try {
        const ivArray = base64ToArrayBuffer(iv);
        const encryptedArray = base64ToArrayBuffer(encryptedData);

        if (ivArray.length === 0 || encryptedArray.length === 0) {
            throw new Error('Decoded data is empty due to base64 decode failure.');
        }

        const decryptedContent = await window.crypto.subtle.decrypt(
            { name: "AES-GCM", iv: ivArray },
            symmKey,
            encryptedArray
        );

        const decryptedMessage = new TextDecoder().decode(decryptedContent);
        return decryptedMessage;
    } catch (error) {
        console.error('Error decrypting message:', error);
        return "Error decrypting message";
    }
}


function arrayBufferToBase64(buffer) {
    return btoa(new Uint8Array(buffer).reduce((data, byte) => data + String.fromCharCode(byte), ''));
}



function base64ToArrayBuffer(base64) {
    try {
        const binaryString = window.atob(base64);
        const len = binaryString.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binaryString.charCodeAt(i);
        }
        return bytes.buffer;
    } catch (e) {
        console.error('Failed to decode Base64 string:', base64, e);
        return new Uint8Array();
    }
}

async function encryptSymmetricKey(symmetricKey, publicKeyBase64) {
    try {
        const publicKeyBinary = window.atob(publicKeyBase64);
        const publicKeyArrayBuffer = new Uint8Array(publicKeyBinary.length).map((_, i) => publicKeyBinary.charCodeAt(i));

        // Import the public key
        const publicKey = await window.crypto.subtle.importKey(
            "spki",
            publicKeyArrayBuffer,
            {
                name: "RSA-OAEP",
                hash: "SHA-256",
            },
            true,
            ["encrypt"]
        );

        const exportedSymmetricKey = await window.crypto.subtle.exportKey("raw", symmetricKey);

        const encryptedSymmetricKey = await window.crypto.subtle.encrypt(
            {
                name: "RSA-OAEP",
            },
            publicKey,
            exportedSymmetricKey
        );

        return arrayBufferToBase64(encryptedSymmetricKey);
    } catch (error) {
        console.error('Error encrypting symmetric key:', error);
        throw error;
    }
}





async function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    if (!selectedFriendId || !message) {
        return;
    }

    try {
        const symmetricKey = await generateSymmetricKey();

        const { encryptedMessage, iv } = await encryptMessage(message, symmetricKey);

        const senderPublicKey = await getPublicKeyById(authUserId);
        const recipientPublicKey = await getPublicKeyById(selectedFriendId);

        const senderEncryptedSymmetricKey = await encryptSymmetricKey(symmetricKey, senderPublicKey);
        const recipientEncryptedSymmetricKey = await encryptSymmetricKey(symmetricKey, recipientPublicKey);

        fetch('/send-message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                recipient_id: selectedFriendId,
                content: encryptedMessage,
                iv: iv,
                sender_symm_key_enc: senderEncryptedSymmetricKey,
                recipient_symm_key_enc: recipientEncryptedSymmetricKey,
            }),
        })
        .then(response => response.json())
        .then(data => {
            openChat(selectedFriendId);
        })
        .catch(error => console.error('Error sending message:', error));

        messageInput.value = '';
    } catch (error) {
        console.error('Encryption error:', error);
    }
}




document.addEventListener('DOMContentLoaded', () => {
    loadFriends();
    document.getElementById('sendButton').addEventListener('click', sendMessage);
    });

</script>
