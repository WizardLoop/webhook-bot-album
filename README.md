# Telegram Album Bot (Webhook-Based)
## webhook-bot-album
A lightweight and powerful Telegram bot that collects **albums, media, and text messages** and send them, in a clean, organized way.

Created by **@wizardloop**.

---

## 🚀 Features

- Collect unlimited messages before sending
- Supports **photos, videos, animations, documents, video notes, and text**
- Supports **albums (media groups)**
- Live counter showing how many messages were collected
- Inline button: **Done**
- Automatic cleanup after sending
- Webhook-based (no polling)
- Simple, clean PHP code
- Fully deployable on any hosting

---

## 📂 File Structure

```
/
├── bot.php                # Main bot script
├── README.md              # You are here
├── data/                  # Auto-created storage for users
│   ├── media<id>.json
│   ├── var<id>.txt
│   └── msgfile<id>.txt
└── examples/
    ├── update.json        # Example incoming update
    └── album.json         # Example album update
```

---

## 🧩 Requirements

- PHP 7.4+
- HTTPS-enabled hosting/server
- Valid Telegram bot token

---

## 📌 Installation

1. Clone the repository:

```sh
git clone https://github.com/WizardLoop/webhook-bot
cd webhook-bot
```

2. Open **bot.php** and set:

```php
define('API_KEY', 'YOUR_BOT_TOKEN');
$adminx = "YOUR_TELEGRAM_ID";
```

3. Upload all files to your server.

---

## 🌐 Setting Your Webhook

Replace:

- `YOUR_BOT_TOKEN` with your bot token  
- `YOUR_DOMAIN/BOT_PATH/bot.php` with your full file URL  

Run in browser:

```
https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook?url=YOUR_DOMAIN/BOT_PATH/bot.php
```

Example:

```
https://api.telegram.org/bot123456:ABC/setWebhook?url=https://mydomain.com/bot/bot.php
```

To delete webhook:

```
https://api.telegram.org/botYOUR_BOT_TOKEN/deleteWebhook
```

---

## 🎯 Usage

User sends:

```
/album
```

Bot responds:

```
Send messages or albums now
Supported: Text, Photo, Video, Animation, Document
```

User sends media → bot deletes it and counts it.

When the user clicks **Done**, the bot send everything to special chat.

---

## 🧪 Webhook Tester (Curl Example)

Send fake update:

```sh
curl -X POST -H "Content-Type: application/json"      -d @examples/update.json      https://yourdomain.com/bot/bot.php
```

---

## 📝 Example JSON Update (Text)

`examples/update.json`:

```json
{
  "update_id": 100000001,
  "message": {
    "message_id": 5,
    "from": { "id": 111, "first_name": "TestUser" },
    "chat": { "id": 111, "type": "private" },
    "date": 1600000000,
    "text": "Hello world"
  }
}
```

---

## 🖼 Example Album Update (Media Group)

`examples/album.json`:

```json
{
  "update_id": 100000002,
  "message": {
    "message_id": 6,
    "media_group_id": "1234567890",
    "from": { "id": 111, "first_name": "TestUser" },
    "chat": { "id": 111, "type": "private" },
    "photo": [
      { "file_id": "FILE_ID_1" },
      { "file_id": "FILE_ID_2" }
    ],
    "caption": "Album photo"
  }
}
```

---

## 🤝 Contributing

Pull requests are welcome.  
Please open an issue before major changes.

---

## 📜 License

MIT License © WizardLoop
