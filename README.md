# 🧠 Ollama AI Chat API – Laravel Backend

This is a Laravel-based API backend that integrates with Ollama AI to provide an authenticated chat experience with persistent user chat history. 

## 🚀 Features

- ✅ User Authentication (register, login, token-based auth) 
- 💬 Chat with Ollama AI
- 📜 Persistent Chat History for authenticated users
- 🔐 Secure API access with Laravel Sanctum
- 🐳 Dockerised for consistent development and deployment
- 🖥️ Herd support for smooth local development

## 🛠️ Tech Stack

- Backend Framework: Laravel (PHP)
- AI Integration: Ollama (local AI model backend)
- Authentication: Laravel Sanctum
- Containerization: Docker + Docker Compose
- Local Dev: Laravel Herd
- Database: MariaDB
- API Type: RESTful

## API EndPoints 
### <span style="color:forestgreen;">**GET**</span> **Welcome**
```text
http://laravel-api.test/api/
```
Simple welcome text 
### <span style="color:#8B5E3C;">**POST**</span> **Register**
```text
http://laravel-api.test/api/register
```
To register a new user - email and password are required
#### Body <span style="font-weight:200;">raw (json)</span>
```JSON
{
    "name": "Your_name",
    "email": "email@email.com",
    "password": "password",
    "password_confirmation": "password"
}
```
#### Expected Output 
```JSON
{
    "message": "New user registered"
}
```
### <span style="color:#8B5E3C;">**POST**</span> **Log In**
```bash
http://laravel-api.test/api/login
```
Log in using your email and password
#### Body <span style="font-weight:200;">raw (json)</span>
```JSON
{
    "email": "email@email.com",
    "password": "password"
}
```
#### Expected Output 
```JSON
{
    "accessToken": "your_access_token"
}
```
### <span style="color:forestgreen;">**GET**</span> **Log Out**
```bash
http://laravel-api.test/api/logout
```
Log out using bearer token
### Authorization

**Type:** Bearer Token  
**Token:** `<token>`

### Request Headers

**Accept:** `application/json`
#### Expected Output 
```JSON
{
    "message": "Logged out"
}
```
### <span style="color:forestgreen;">**GET**</span> **Get User**
```bash
http://laravel-api.test/api/user
```
Get a user based on their active token
### Authorization

**Type:** Bearer Token  
**Token:** `<token>`
#### Expected Output
```JSON 
{
    "id": 7,
    "name": "Your_name",
    "email": "email@email.com",
    "email_verified_at": null,
    "created_at": "2025-04-12T11:32:56.000000Z",
    "updated_at": "2025-04-12T11:32:56.000000Z"
}
```
### <span style="color:#8B5E3C;">**POST**</span> **Chat No Log In**
```bash
http://laravel-api.test/api/chat_free
```
#### Body <span style="font-weight:200;">raw (json)</span>
```JSON
{
    "message": "your_message"
}
```
#### Expected Outcome
```JSON
{
    "response": "Hey there! How can I assist you today?"
}
```
### <span style="color:#8B5E3C;">**POST**</span> **Chat Logged In**
```bash
http://laravel-api.test/api/chat
```
Logged in users may generate a session_id after there first message with a bearer token and then start a chat history when it is included with the message. 
### Authorization

**Type:** Bearer Token  
**Token:** `<token>`
#### Body <span style="font-weight:200;">raw (json)</span>
```JSON
{
    "message": "your_message",
    "session_id": "your session_id after first message"
}
```