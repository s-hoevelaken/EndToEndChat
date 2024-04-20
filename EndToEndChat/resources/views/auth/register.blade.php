<script>
    /**
     * generate a public and a private key using Web Cryptography API and indexedDB
     *
     * 
     * 
     * @param  array<string, string>  $input
    */

    // Function to generate a public-private key pair
    async function generateKeyPair() {
        const keyPair = await window.crypto.subtle.generateKey(
            {
                name: "RSA-OAEP",
                modulusLength: 2048,
                publicExponent: new Uint8Array([1, 0, 1]),
                hash: "SHA-256",
            },
            true, // Set extractable to true to allow exporting the private key
            ["encrypt", "decrypt"]
        );
        return keyPair;
    }

    // Function to export the public key to a usable format
    async function exportPublicKey(key) {
        const exported = await window.crypto.subtle.exportKey(
            "spki",
            key
        );
        const exportedAsBase64 = btoa(String.fromCharCode(...new Uint8Array(exported)));
        return exportedAsBase64;
    }


    async function savePrivateKeyInIndexedDB(privateKey) {
        // Export the private key to a format suitable for storage
        const exportedKey = await window.crypto.subtle.exportKey("jwk", privateKey);

        let DBDeleteRequest = window.indexedDB.deleteDatabase("CryptoDB");


        const request = indexedDB.open('CryptoDB', 1);

        request.onupgradeneeded = function(event) {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('Keys')) {
                db.createObjectStore('Keys', { keyPath: 'id' });
            }
        };

        request.onerror = function(event) {
            console.error('Database error:', event.target.errorCode);
        };

        request.onsuccess = function(event) {
            const db = event.target.result;
            const transaction = db.transaction(['Keys'], 'readwrite');
            const store = transaction.objectStore('Keys');
            const putRequest = store.put({ id: 'PrivateKey', value: exportedKey });

            putRequest.onsuccess = function() {
                console.log('Private key stored successfully');
            };

            putRequest.onerror = function(event) {
                console.error('Error storing private key:', event.target.errorCode);
            };
        };
    }

        

    // Main function to handle the key generation and setup
    async function setupKeys() {
        try {
            const keyPair = await generateKeyPair();
            const publicKeyBase64 = await exportPublicKey(keyPair.publicKey);

            // Set the public key in the hidden input field
            document.getElementById('publicKey').value = publicKeyBase64;

            // Save the private key in IndexedDB
            await savePrivateKeyInIndexedDB(keyPair.privateKey);
        } catch (error) {
            console.error("Error generating or setting keys:", error);
        }
    }

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('registrationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        console.log("we startin");

        try {
            const keyPair = await generateKeyPair();
            const publicKeyBase64 = await exportPublicKey(keyPair.publicKey);
            document.getElementById('publicKey').value = publicKeyBase64;

            await savePrivateKeyInIndexedDB(keyPair.privateKey);

            this.submit();
        } catch (error) {
            console.error("Error generating or setting keys:", error);
        }
    });
});

</script>







<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form id="registrationForm" method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                    autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
            </div>

            <!-- Hidden field for the public key -->
            <input type="hidden" name="publicKey" id="publicKey">


            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                    'terms_of_service' =>
                                        '<a target="_blank" href="' .
                                        route('terms.show') .
                                        '" class="underline text-sm text-gray-600 hover:text-gray-900">' .
                                        __('Terms of Service') .
                                        '</a>',
                                    'privacy_policy' =>
                                        '<a target="_blank" href="' .
                                        route('policy.show') .
                                        '" class="underline text-sm text-gray-600 hover:text-gray-900">' .
                                        __('Privacy Policy') .
                                        '</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
