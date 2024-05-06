// import './bootstrap';

window.openTab = function(evt, tabName) {
let i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    document.getElementById('dynamicDisplay').innerHTML = "";

    evt.currentTarget.className += " active";
}

window.loadReceivedRequests = function() {
    fetch('/api/friend-requests/received', {
        headers: {
            'Accept': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        const list = document.getElementById('dynamicDisplay');
        console.log('Checking if dynamicDisplay exists:', list);
        list.innerHTML = '';
        data.forEach(request => {
            console.log(request);
            const li = document.createElement('li');
            li.textContent = `${request.requester_name} - Status: ${request.status} `;

            const buttonContainer = document.createElement('div');
            buttonContainer.style.display = 'flex';
            buttonContainer.style.gap = '10px';

            const acceptButton = document.createElement('button');
            acceptButton.textContent = 'Accept';
            acceptButton.className = 'AcceptBtn';
            acceptButton.onclick = function() { acceptFriendRequest(request.friendship_id); };

            const rejectButton = document.createElement('button');
            rejectButton.textContent = 'Reject';
            rejectButton.className = 'RejectBtn';
            rejectButton.onclick = function() { rejectFriendRequest(request.friendship_id); };

            buttonContainer.appendChild(acceptButton);
            buttonContainer.appendChild(rejectButton);

            li.appendChild(buttonContainer);
            list.appendChild(li);
        });


        // fakes voor looks  
        for (let n = 0; n < 9; n++) {
            const li = document.createElement('li');
            li.textContent = `fake${n} - Status: pedning `;

            const buttonContainer = document.createElement('div');
            buttonContainer.style.display = 'flex';
            buttonContainer.style.gap = '10px';

            const acceptButton = document.createElement('button');
            acceptButton.textContent = 'Accept';
            acceptButton.className = 'AcceptBtn';

            const rejectButton = document.createElement('button');
            rejectButton.textContent = 'Reject';
            rejectButton.className = 'RejectBtn';

            buttonContainer.appendChild(acceptButton);
            buttonContainer.appendChild(rejectButton);

            li.appendChild(buttonContainer);
            list.appendChild(li);
        };

    })
    .catch(error => {
        console.error('Error loading received friend requests:', error);
    });
}

                
window.loadSentRequests = function() {
    fetch('/api/friend-requests/sent')
    .then(response => response.json())
    .then(data => {
        const list = document.getElementById('dynamicDisplay');
        list.innerHTML = '';
        data.forEach(request => {
            console.log(data)
            const li = document.createElement('li');
            li.textContent = `To: ${request.addressee_name} - Status: ${request.status}`;

            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Cancel';
            cancelButton.onclick = function() { cancelFriendRequest(request.friendship_id); };

            li.appendChild(cancelButton);
            list.appendChild(li);
        });


        // fakes for looks:

        for (let m = 0; m < 11; m++) {
            const li = document.createElement('li');
            li.textContent = `To: fake${m} - Status: cancel`;

            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Cancel';

            li.appendChild(cancelButton);
            list.appendChild(li);
        };
    });
}

window.loadBlockedUsers = function() {
    fetch('/api/blocked-users')
    .then(response => response.json())
    .then(data => {
        const list = document.getElementById('dynamicDisplay');
        list.innerHTML = '';
            data.blockedUsers.forEach(user => {
            const li = document.createElement('li');
            li.textContent = `Username: ${user.name}`;

            const unblockButton = document.createElement('button');
            unblockButton.textContent = 'Unblock';
            unblockButton.onclick = function() { unblockUser(user.id); };

            li.appendChild(unblockButton);
            list.appendChild(li);
        });

        // Adding fake data for visual
        for (let i = 0; i < 10; i++) {
            const li = document.createElement('li');
            li.textContent = `Username: fake${i}`;

            const unblockButton = document.createElement('button');
            unblockButton.textContent = 'Unblock';
            unblockButton.onclick = function() { console.log(`Unblock fake${i} clicked`); };

            li.appendChild(unblockButton);
            list.appendChild(li);
        }
    })
    .catch(error => {
        console.error('Error loading blocked users:', error);
    });
}

function acceptFriendRequest(requestId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/friend-requests/accept/${requestId}`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        credentials: 'same-origin',
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        }
        loadReceivedRequests();
    })
    .catch(error => {
        console.error('Error accepting friend request:', error);
        alert('Failed to accept friend request.');
    });
}





function rejectFriendRequest(requestId) {
    console.log(requestId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/api/friend-requests/reject/${requestId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        }
        loadReceivedRequests();
    })
    .catch(error => {
        console.error('Error rejecting friend request:', error);
        alert('Failed to reject friend request.');
    });
}

function cancelFriendRequest(requestId) {
    console.log("Canceling friend request with ID:", requestId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/api/friend-requests/cancel/${requestId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
        }
        loadSentRequests();
    })
    .catch(error => {
        console.error('Error canceling friend request:', error);
        alert('Failed to cancel friend request.');
    });
}

let animationIsToggled = false;
document.addEventListener('DOMContentLoaded', () => {
    loadFriends();
    setupMessageSending();
    setupMessageScroll();

    const animationButton = document.getElementById('animationButton');
    const inputContainer = document.createElement('div');
    inputContainer.id = 'inputContainer';
    inputContainer.style.display = 'none';
    animationButton.appendChild(inputContainer);

    animationButton.addEventListener('click', (event) => {
        if (!animationIsToggled) {
            animationIsToggled = true;
            animationButton.classList.add('square');
            loadSquareContent();
            document.querySelector('.tablinks').click();
            event.stopPropagation();
        }
    });

    document.addEventListener('click', (event) => {
        if (animationIsToggled && !animationButton.contains(event.target)) {
            animationIsToggled = false;
            animationButton.classList.remove('square');
            inputContainer.style.display = 'none';
            inputContainer.innerHTML = '';
        }
    });
});






function setupMessageScroll() {
const chatMessages = document.querySelector('.chat-messages');
const threshold = 150;

chatMessages.addEventListener('scroll', async function() {
    if (this.scrollTop < threshold && currentPage <= totalPages && !isChatLoading) {
        await openChat(selectedFriendId, true);
    }
});
}



function setupMessageSending() {
const messageInput = document.getElementById('messageInput');

messageInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        sendMessage();
        event.preventDefault();
    }
});
}

function toggleAnimation(animationButton, inputContainer) {
let animationIsToggled = animationButton.classList.contains('square');
if (!animationIsToggled) {
    animationButton.classList.add('square');
    loadSquareContent();
    setupNameInput();
    document.querySelector('.tablinks').click();
} else {
    animationButton.classList.remove('square');
    inputContainer.style.display = 'none';
    inputContainer.innerHTML = '';
}
}



function loadSquareContent() {
    const inputContainer = document.getElementById('inputContainer');
    inputContainer.innerHTML = `
    <div class="form__group field">
        <input type="text" class="form__field" placeholder="" name="name" id="name" required />
        <label for="name" class="form__label">add friends</label>
    </div>

    <div class="tab">
        <button id='Received' class="tablinks" onclick="loadReceivedRequests(); openTab(event, 'Received')">Received</button>
        <button id='Sent' class="tablinks" onclick="loadSentRequests(); openTab(event, 'Sent')">Sent</button>
        <button id='Blocked' class="tablinks" onclick="loadBlockedUsers(); openTab(event, 'Blocked')">Blocked</button>
    </div>

    <div id="dynamicDisplay">

    </div>
    `;
    inputContainer.style.display = 'flex';
    setupNameInput();
}

function setupNameInput() {
    const nameInput = document.getElementById('name');
    if (nameInput) {
        nameInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                const nameContent = nameInput.value;
                sendFriendRequest(nameContent);
                nameInput.value = '';
                event.preventDefault();
            }
        });
    }
    }





async function sendFriendRequest(name) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const response = await fetch('/friend-request/by-name', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ name }),
        });

        if (response.ok) {
            const data = await response.json();
            alert('Friend request sent successfully.');
        } else {
            if (response.status === 409) {
                alert('A friend request has already been sent or you are already friends.');
            } else if (response.status === 422) {
                const errorData = await response.json();
                const errors = Object.values(errorData.errors).map(err => err.join(', ')).join('\n');
                alert(`Validation errors: \n${errors}`);
            } else {
                const errorData = await response.json();
                alert(`Error: ${errorData.message}`);
            }
        }
    } catch (error) {
        console.error('Unexpected error sending friend request:', error);
        alert('An unexpected error occurred while sending the friend request.');
    }
}
    


async function getLastUpdateInfriendship(friendId) {
    try {
        const response = await fetch(`/api/friendship-info/${friendId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return data.updatedAt;
    } catch (error) {
        console.error('Failed to fetch friendship data:', error);
    }
}
    


async function fetchLastMessage(friendId) {
    try {
        const response = await fetch(`/api/last-message/${friendId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        const data = await response.json();
        if (!data.data || !data.data.content) {
            let lastUpdate = await getLastUpdateInfriendship(friendId);
            return { 
                message: 'No messages', 
                timeForDisplay: '',
                timeForOrder: lastUpdate || new Date(0),
            };
        }


        const privateKey = await getPrivateKey();
        const symmKeyEnc = data.data.sender_id === authUserId ? data.data.sender_symm_key_enc : data.data.recipient_symm_key_enc;
        const decryptedSymmKey = await decryptSymmetricKey(symmKeyEnc, privateKey);
        const decryptedMessage = await decryptMessage(data.data.content, decryptedSymmKey, data.data.iv);

        const maxLengthOfPreview = 35;
        const truncatedMessage = decryptedMessage.length > maxLengthOfPreview ? decryptedMessage.substring(0, maxLengthOfPreview) + '...' : decryptedMessage;
        const timeSinceSent = timeSince(data.data.created_at);

        return { message: truncatedMessage, timeForDisplay: timeSinceSent, timeForOrder: data.data.created_at };
    } catch (error) {
        console.error('Error fetching the last message:', error);
        return { message: 'No messages', time: '' };
    }
}


async function removeFriend(friendId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    try {
        const response = await fetch(`/api/remove-friend/${friendId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        });

        const data = await response.json();
        if (data.message) {
            alert(data.message);
        }

        loadFriends();
    } catch (error) {
        console.error('Error removing friend:', error);
        alert('Failed to remove friend.');
    }
}






let selectedFriendId = null;
let readStatusDisplayed = false;

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
        const enrichedFriends = await Promise.all(data.map(async friend => {
            const lastMessageData = await fetchLastMessage(friend.id);
            return {
                ...friend,
                lastMessageContent: lastMessageData.message || 'No messages',
                lastMessageTimeForOrder: lastMessageData.timeForOrder || ' '
            };
        }));
        


        enrichedFriends.sort((a, b) => {
            return new Date(b.lastMessageTimeForOrder) - new Date(a.lastMessageTimeForOrder);
        });

        for (const friend of enrichedFriends) {

            const lastMessageData = await fetchLastMessage(friend.id);

            const li = document.createElement('li');


            li.className = `flex items-center justify-between px-6 h-20 cursor-pointer transition-colors duration-300 ${friend.id === selectedFriendId ? 'selectedFriendGradient bg-hoverFriendsListColor border-l-4 border-customBlue' : 'bg-mainBackgroundColor hover:bg-hoverFriendsListColor'}`;

            const nameDetailsDiv = document.createElement('div');
            nameDetailsDiv.className = 'flex flex-col flex-grow';

            const nameDiv = document.createElement('div');
            nameDiv.className = 'font-semibold text-md text-FriendNameTextColor';
            nameDiv.textContent = friend.name;

            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'text-lastMessageColorFriendsList text-xs';
            detailsDiv.textContent = `${lastMessageData.message} • ${lastMessageData.timeForDisplay}`;

            nameDetailsDiv.appendChild(nameDiv);
            nameDetailsDiv.appendChild(detailsDiv);

            const dropdownDiv = document.createElement('div');
            dropdownDiv.className = 'relative';

            const dropdownButton = document.createElement('button');
            dropdownButton.className = 'dropdown-button text-gray-500 hover:text-gray-700 focus:outline-none';
            dropdownButton.innerHTML = '⋮';

            const dropdownContent = document.createElement('div');
            dropdownContent.className = 'hidden absolute right-0 w-40 bg-white shadow-lg rounded-md z-10 mt-1';
            
            const removeOption = document.createElement('a');
            removeOption.href = '#';
            removeOption.className = 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md';
            removeOption.textContent = `Remove`;
            removeOption.onclick = function (event) {
                event.preventDefault();
                removeFriend(friend.id);
            };

            const blockOption = document.createElement('a');
            blockOption.href = '#';
            blockOption.className = 'block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md';
            blockOption.textContent = `Block`;
            blockOption.onclick = function(event) {
                event.preventDefault();
                blockUser(friend.id);
            };


            dropdownContent.appendChild(removeOption);
            dropdownContent.appendChild(blockOption);

            dropdownDiv.appendChild(dropdownButton);
            dropdownDiv.appendChild(dropdownContent);

            dropdownButton.onclick = function(event) {
                event.stopPropagation();
                dropdownContent.classList.toggle('hidden');
            };

            li.appendChild(nameDetailsDiv);
            li.appendChild(dropdownDiv);

            li.addEventListener('click', () => {
                openChat(friend.id);
            });
            list.appendChild(li);
        }

    } catch (error) {
        console.error('Error loading friends:', error);
    }
}



async function blockUser(blockedId) {
    try {
        const response = await fetch('/api/block', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ blocked_id: blockedId })
        });
        const data = await response.json();
        if (data.message) {
            alert(data.message);
        }
        loadFriends();
    } catch (error) {
        console.error('Error blocking user:', error);
        alert('Failed to block user.');
    }
}

async function unblockUser(blockedId) {
    try {
        const response = await fetch('/api/unblock', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ blocked_id: blockedId })
        });
        const data = await response.json();
        if (data.message) {
            alert(data.message);
        }
        loadFriends();
    } catch (error) {
        console.error('Error unblocking user:', error);
        alert('Failed to unblock user.');
    }
}




async function markMessageAsRead(messageId) {
    try {
        const response = await fetch(`/api/messages/read/${messageId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Failed to mark message as read');
        }
        console.log('Message marked as read:', data);
    } catch (error) {
        console.error('Error marking message as read:', error);
    }
}


let currentPage = 1;
let totalPages = 1;
let isChatLoading = false;

async function openChat(friendId, keepScrollPosition = false) {
    if (isChatLoading) {
        console.log("Attempt to open another chat while previous is loading: ", friendId);
        return;
    }
    isChatLoading = true;

    console.log("Attempting to open chat with friend ID:", friendId);

    if (selectedFriendId !== friendId) {
        console.log("Switching to new chat, clearing messages for friend ID:", selectedFriendId);
        document.querySelector('.chat-messages').innerHTML = '';
        currentPage = 1;
        selectedFriendId = friendId;
        readStatusDisplayed = false;
        totalPages = 1;
        loadFriends();
    }

    if (currentPage > totalPages) {
        console.log('All messages have been loaded for friend ID:', friendId);
        isChatLoading = false;
        return;
    }

    try {
        const response = await fetch(`/api/chat/${friendId}?page=${currentPage}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        const result = await response.json();
        if (!result.data || result.data.length === 0) {
            console.log('No more messages to load for friend ID:', friendId);
            isChatLoading = false;
            return;
        }

        currentPage += 1;
        totalPages = result.last_page;
        console.log("Loaded messages page:", currentPage - 1, "for friend ID:", friendId);

        const messages = result.data;
        const privateKey = await getPrivateKey();
        const messagesContainer = document.querySelector('.chat-messages');
        let oldScrollHeight = messagesContainer.scrollHeight;
        let lastReadMessageFound = false;
        let lastReadMessageElement = null;

        for (let i = 0; i < messages.length; i++) {
            const message = messages[i];
            let symmKeyEnc = message.sender_id === authUserId ? message.sender_symm_key_enc : message.recipient_symm_key_enc;
            let decryptedSymmKey = await decryptSymmetricKey(symmKeyEnc, privateKey);
            let decryptedMessage = await decryptMessage(message.content, decryptedSymmKey, message.iv);

            const messageElement = document.createElement('div');
            const messageText = document.createElement('p');
            messageText.textContent = decryptedMessage;

            messageElement.appendChild(messageText);

            if (message.sender_id === authUserId) {
                messageElement.className = 'chat-msg mr-2 owner dark:bg-blue-600 dark:text-white p-3 rounded-tl-2xl rounded-tr-2xl rounded-bl-2xl rounded-br-none max-w-2xl ml-auto mb-1';
                if (message.is_read && !lastReadMessageFound) {
                    lastReadMessageFound = true;
                    lastReadMessageElement = messageElement;
                }
            } else {
                messageElement.className = 'chat-msg received bg-gray-200 dark:bg-gray-700 text-black dark:text-white p-3 rounded-tr-2xl rounded-tl-2xl rounded-br-2xl rounded-bl-none max-w-2xl mr-auto mb-1';
            }

            messagesContainer.prepend(messageElement);

            if (message.recipient_id === authUserId && !message.is_read) {
                markMessageAsRead(message.id);
            }
        }

        if (lastReadMessageElement) {
            const readIndicator = document.createElement('div');
            readIndicator.textContent = 'Message seen';
            readIndicator.className = 'text-xs text-gray-500 ml-auto mr-4';
            lastReadMessageElement.parentNode.insertBefore(readIndicator, lastReadMessageElement.nextSibling);
        }

        if (keepScrollPosition) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight - oldScrollHeight;
        } else {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    } catch (error) {
        console.error('Error opening chat:', error);
    } finally {
        isChatLoading = false;
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
    const iv = window.crypto.getRandomValues(new Uint8Array(12));

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

function timeSince(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    let interval = seconds / 31536000;

    if (interval > 1) {
        return Math.floor(interval) + "y";
    }
    interval = seconds / 2592000;
    if (interval > 1) {
        return Math.floor(interval) + "mo";
    }
    interval = seconds / 86400;
    if (interval > 1) {
        return Math.floor(interval) + "d";
    }
    interval = seconds / 3600;
    if (interval > 1) {
        return Math.floor(interval) + "h";
    }
    interval = seconds / 60;
    if (interval > 1) {
        return Math.floor(interval) + "m";
    }
    return Math.floor(seconds) + "s";
}




function updateSelectedFriend(newSelectedFriendId) {
    if (selectedFriendId !== null) {
        const oldSelected = document.getElementById(`friend-${selectedFriendId}`);
        if (oldSelected) {
            oldSelected.className = 'h-20 px-6 hover:bg-hoverFriendsListColor cursor-pointer text-white transition-colors duration-300 flex flex-col justify-center';
        }
    }
    const newSelected = document.getElementById(`friend-${newSelectedFriendId}`);
    if (newSelected) {
        newSelected.className = 'h-20 px-6 hover:bg-hoverFriendsListColor cursor-pointer text-white transition-colors duration-300 flex flex-col justify-center border-l-4 border-customBlue';
    }
    selectedFriendId = newSelectedFriendId; 
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

        const response = await fetch('/send-message', {
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
        });

        const data = await response.json();
        if (response.ok) {
            const decryptedMessage = await decryptMessage(data.data.content, symmetricKey, iv);
            displayMessage(decryptedMessage, true);
            messageInput.value = '';
        } else {
            throw new Error(data.message || 'Failed to send message');
        }
    } catch (error) {
        console.error('Encryption error or sending failed:', error);
    }
}

function displayMessage(message, isSender) {
    const messagesContainer = document.querySelector('.chat-messages');
    const messageElement = document.createElement('div');
    messageElement.classList.add('message');
    messageElement.textContent = message;
    if (isSender) {
        messageElement.className = 'chat-msg owner dark:bg-blue-600 dark:text-white p-3 rounded-tl-2xl rounded-tr-2xl rounded-bl-2xl rounded-br-none max-w-2xl ml-auto mb-1';
    } else {
        messageElement.className = 'chat-msg received bg-gray-200 dark:bg-gray-700 text-black dark:text-white p-3 rounded-tr-2xl rounded-tl-2xl rounded-br-2xl rounded-bl-none max-w-2xl mr-auto mb-1';
    }
    messagesContainer.appendChild(messageElement);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}