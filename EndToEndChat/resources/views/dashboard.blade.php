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
</div>
@endsection
<style>
    .main-content {
    
    height: calc(100vh - 65px) !important;
    overflow-y: hidden;
}
</style>

<script>
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
                    console.log(`Open chat with ${friend.name}`);
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
        const messages = await response.json();

        const messagesContainer = document.querySelector('.chat-messages'); // Assuming you have this container for messages
        messagesContainer.innerHTML = ''; // Clear previous messages

        messages.forEach(message => {
            const messageElement = document.createElement('div');
            messageElement.classList.add('message');
            // You might want to differentiate between sent and received messages here
            messageElement.textContent = `${message.content}`; // Adjust based on your message object
            console.log(message.content);
            messagesContainer.appendChild(messageElement);
        });
    } catch (error) {
        console.error('Error opening chat:', error);
    }
}


function sendMessage() {
    const message = document.getElementById('messageInput').value;
    if (!selectedFriendId || !message.trim()) {
        // Optionally handle the case where no friend is selected or message is empty
        return;
    }
    document.getElementById('messageInput').value = ''; // Clear the input after sending the message
    
    fetch('/send-message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ recipient_id: selectedFriendId, content: message }),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Message sent:', data);
        // Refresh or update chat messages here
        openChat(selectedFriendId);
    })
    .catch(error => console.error('Error sending message:', error));
}





document.addEventListener('DOMContentLoaded', () => {
    loadFriends();
    document.getElementById('sendButton').addEventListener('click', sendMessage);
    });

</script>
