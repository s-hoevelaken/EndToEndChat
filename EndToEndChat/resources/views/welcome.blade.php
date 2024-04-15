<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <script defer>

            @if(auth()->check())
                const userId = {{ auth()->id() }};
            @else
                let userId = null;
            @endif
            
            function loadSentRequests() {
                fetch('/api/friend-requests/sent')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('sentRequestsList');
                    list.innerHTML = '';
                    data.forEach(request => {
                        const li = document.createElement('li');
                        li.textContent = `To: ${request.addressee_id} - Status: ${request.status}`;
                        list.appendChild(li);
                    });
                });
            }

            function loadReceivedRequests() {
                fetch('/api/friend-requests/received', {
                    headers: {
                        'Accept': 'application/json',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('receivedRequestsList');
                    list.innerHTML = '';
                    data.forEach(request => {
                        const li = document.createElement('li');
                        li.textContent = `From: ${request.requester_id} - Status: ${request.status} `;

                        const acceptButton = document.createElement('button');
                        acceptButton.textContent = 'Accept';
                        acceptButton.onclick = function() { acceptFriendRequest(request.id); };

                        // Append the Accept button to the list item
                        li.appendChild(acceptButton);

                        // Append the list item to the list
                        list.appendChild(li);
                    });
                })
                .catch(error => {
                    console.error('Error loading received friend requests:', error);
                });
            }


            
            function loadFriends() {
                fetch('/api/friends')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('friendsList');
                    list.innerHTML = '';
                    data.forEach(friend => {
                        const friendId = friend.addressee_id === userId ? friend.requester_id : friend.addressee_id;
                        const li = document.createElement('li');
                        li.textContent = `Friend ID: ${friend.id}\nFriend name: ${friend.name}`;
                        list.appendChild(li);
                    });
                });
            }



            async function sendFriendRequest() {
                const name = document.getElementById('name').value;

                fetch('/friend-request/by-name', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        name: name,
                    }),
                })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 422) {
                            return response.json().then(data => {
                                throw new Error(Object.values(data.errors).join('\n'));
                            });
                        }
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    alert('Friend request sent successfully.');
                })
                .catch(error => {
                    console.error('Error sending friend request:', error);
                    alert(error.message);
                });
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
                            console.log('Retrieved private key:', getRequest.result);
                            resolve(getRequest.result);
                        };

                        getRequest.onerror = function(event) {
                            console.error('Error retrieving private key:', event.target.error);
                            reject(event.target.error);
                        };
                    };
                });
            }

            function testRetrievePrivateKey() {
                retrievePrivateKeyFromIndexedDB()
                .catch((error) => {
                    console.error('Error retrieving private key:', error);
                });
            }
            
            function clearKeysObjectStore() {
                const request = indexedDB.open('CryptoDB', 1);

                request.onsuccess = function(event) {
                    const db = event.target.result;
                    const transaction = db.transaction(['Keys'], 'readwrite');
                    const store = transaction.objectStore('Keys');
                    const clearRequest = store.clear();

                    clearRequest.onsuccess = function() {
                        console.log('Keys object store cleared successfully');
                    };

                    clearRequest.onerror = function(error) {
                        console.error('Error clearing Keys object store:', error);
                    };
                };

                request.onerror = function(event) {
                    console.error('Database error:', event.target.errorCode);
                };
            }

            function acceptFriendRequest(requestId) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/friend-requests/accept/${requestId}`, { // Adjust the URL if it's supposed to be an API route
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken, // Include the CSRF token in the request headers
                    },
                    credentials: 'same-origin', // Ensure cookies, including session cookies, are sent with the request
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Friend request accepted:', data);
                    alert('Friend request accepted successfully.');
                    loadReceivedRequests();
                })
                .catch(error => {
                    console.error('Error accepting friend request:', error);
                    alert('Failed to accept friend request.');
                });
            }



        </script>
    </head>

    



    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif



            <div style='display: flex; flex-direction: column;'>
                <h1 class='dark:text-white size-10'>
                    testing functionality
                </h1>
                <div>
                    <button class='dark:text-white' onclick="testRetrievePrivateKey()">1. Test Retrieve Private Key</button>
                </div>
                <div>
                    <button class='dark:text-white' onclick="clearKeysObjectStore()">2. remove keys</button>
                </div>
                
                <input type="text" id="name" placeholder="Enter friend's username">
                <button onclick="sendFriendRequest()">Send Request</button>
            </div>



            <div id="friendRequestsContainer">
                <div id="sentRequests">
                    <h2>Sent Requests</h2>
                    <button onclick="loadSentRequests()">Load Sent Requests</button>
                    <ul id="sentRequestsList"></ul>
                </div>
                <div id="receivedRequests">
                    <h2>Received Requests</h2>
                    <button onclick="loadReceivedRequests()">Load Received Requests</button>
                    <ul id="receivedRequestsList"></ul>
                </div>
                <div id="friends">
                    <h2>Friends</h2>
                    <button onclick="loadFriends()">Load Friends</button>
                    <ul id="friendsList"></ul>
                </div>
            </div>
        </div>
    </body>
</html>
