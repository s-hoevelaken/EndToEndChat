# EndToEndChat
## Introduction
EndToEndChat is a personal project made with the goal of learning more about encryption.

About the encryption, I used a mix of symmetrical and asymmetrical encrpytion to encrypt the messages. The private key gets generated and saved on the client side, so the private key is never exposed to the server. The symmetrical key is saved encrypted by both public keys in the database.

I also used a Initialization Vector (IV) even though this wasnt really needed, since i encrypted every message with its own unique key the chiper text would be unique either way but I added it as part of the learning experience.


## Screenshots

<img src="screenshots/chat screenshot.png" alt="Chat Screenshot" width="800"/>
<img src="screenshots/friends request screenshot.png" alt="Friends Request Screenshot" width="800"/>


## Features
* authentication
* adding friends
* sending messages
* blocking friends
* lazy loading messages for performance



## Technologies
1. MySQL database
2. laravel
3. Jetstream
4. Tailwind css
5. Web Cryptography API
6. IndexedDB



## How to run the project

### 1. Clone the Repository
You can clone the repo from the green '<> code' button

### 2. Install composer
Composer has to be installed on your device already.
```bash
composer install 
```

### 3. Install Node.js Dependencies
Ensure that Node.js and npm are installed. Then, run:

```bash
npm install
```

### 4. Setting up the database
You need to host your MySQL database. This can be done locally or using a cloud provider.
and create a database to connect to using
```bash
CREATE DATABASE <your chosen name>;
```

### 5. Set Up Environment Variables
Copy the .env.example file to .env and configure it with your database and other environment settings.
```bash
cp .env.example .env
```

Edit the .env file to match your local database configuration:

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<your chosen name>
DB_USERNAME=root
DB_PASSWORD=your_database_password
```


### 6. Generate Application Key

```bash
php artisan key:generate
```

### 7. Run Migrations

```bash
php artisan migrate
```

### 8. Install and Build Frontend Assets

```bash
npm run dev
```

### 9. Start the Local Development Server
```
php artisan serve
```

### 10. Access the Application

open the link that was shown from the previous command.


## License

[MIT license](https://opensource.org/licenses/MIT).