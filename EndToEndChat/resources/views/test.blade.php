~<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <button onclick="testRetrievePrivateKey();">
        get key
    </button>
    <button onclick="printAllDataFromIndexedDB();">
        all DB in db
    </button>
    <button onclick="clearKeysObjectStore();">
        remove keys
    </button>
</body>
</html>

<script>

function printAllDataFromIndexedDB() {
    // Open the database with the same version number used in your setup
    const dbRequest = indexedDB.open('CryptoDB', 1);

    dbRequest.onerror = function (event) {
        console.error('Database error:', event.target.error);
    };

    dbRequest.onsuccess = function (event) {
        const db = event.target.result;

        // For each object store in the database, retrieve and print all data
        const objectStoreNames = db.objectStoreNames;
        for (let i = 0; i < objectStoreNames.length; i++) {
            const objectStoreName = objectStoreNames[i];
            const transaction = db.transaction([objectStoreName], 'readonly');
            const store = transaction.objectStore(objectStoreName);
            const request = store.openCursor();

            console.log(`\n--- Contents of object store: ${objectStoreName} ---\n`);
            
            request.onsuccess = function (event) {
                const cursor = event.target.result;
                if (cursor) {
                    console.log(cursor.key, cursor.value);
                    cursor.continue();
                } else {
                    console.log(`End of object store: ${objectStoreName}`);
                }
            };

            request.onerror = function (event) {
                console.error(`Error reading data from object store ${objectStoreName}:`, event.target.error);
            };
        }
    };
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

</script>