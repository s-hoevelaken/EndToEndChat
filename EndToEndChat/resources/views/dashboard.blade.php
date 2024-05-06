@extends('layouts.appTrad')
@section('content')

<div class="flex flex-col main-content bg-mainBackgroundColor">
    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
        <div class="w-1/6 bg-mainBackgroundColor overflow-y-auto  border-r-2 border-borderColorGrey relative" style="overflow: visible; width: 22%;">
            <ul id="friendsList">
                <!-- Dynamic friends will be inserted here -->
            </ul>
            <!-- button that starts animation -->
            <div id='animationButton' class="absolute bottom-0 left-1/2 transform -translate-x-1/2 mb-10 bg-customBlue rounded-full flex items-center justify-center shadow-custom cursor-pointer" style="box-shadow: 0 0 16px #0086ff;">
                <div class="text-white text-2xl text">+</div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 p-10 overflow-y-auto">
            <div class="flex flex-col justify-between h-full">
                <!-- Chat Messages -->
                <div class="chat-messages flex flex-col overflow-y-auto p-2 space-y-reverse space-y-2">
                    <!-- Dynamic Chat Messages Here -->
                </div>
                <!-- Message Input -->
                <div id="messageInputContainer" class="mt-4 flex">
                    <input type="text" id="messageInput" class="w-full text-white border-0 bg-backgroundMessagesField p-2 rounded-lg" placeholder="Type a message...">
                </div>
            </div>
        </div>
    </div>
</div>

@endsection