# Twilio WhatsApp Integration Guide

This document explains how to send **automatic WhatsApp notifications for both client and doctor** using Twilio's WhatsApp API. It complements the existing email system and requires a small server to keep your credentials secure.

---
## Overview

1. **Client submits appointment form** on your static site.
2. Browser sends form data to the server (`/book` endpoint).
3. Server uses Twilio API to send templated WhatsApp messages to:
   * The client’s phone number
   * The doctor’s phone number (`+254759396531`)
4. Server responds back and the browser displays confirmation as usual.

Emails will still be handled by EmailJS on the client side.

---
## Steps

### 1. Create a Twilio Account

1. Sign up at [Twilio](https://www.twilio.com/). The trial gives you a free WhatsApp sandbox number.
2. Verify your phone number.
3. Go to the **WhatsApp** section under "Messaging > Try it out > Send a WhatsApp message" to join the sandbox.
4. Note the sandbox number (it starts with `+1 415 523 8886`) and the **join** code.
5. Send the join message from your WhatsApp to the sandbox number to connect.

### 2. Get Credentials

From the Twilio console:
- **Account SID** (looks like `ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX`)
- **Auth Token** (keep it secret!)
- **WhatsApp-enabled Twilio phone number** (e.g. `whatsapp:+14155238886`)

### 3. Create Server

Create a new folder `server/` at the project root (or anywhere you host Node). Add `package.json` & `server.js`:

```bash
mkdir server
cd server
npm init -y
npm install express body-parser twilio dotenv
```

**server/server.js:**
```js
require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const twilio = require('twilio');

const app = express();
app.use(bodyParser.json());

const client = twilio(process.env.TWILIO_SID, process.env.TWILIO_AUTH);
const fromWa = process.env.TWILIO_WHATSAPP_FROM; // e.g. 'whatsapp:+14155238886'
const doctorWa = 'whatsapp:+254759396531';

app.post('/book', async (req, res) => {
  const b = req.body;
  const msg = `Booking ${b.bookingId} for ${b.petName} on ${b.appointmentDate} at ${b.appointmentTime}`;

  try {
    await Promise.all([
      client.messages.create({ from: fromWa, to: `whatsapp:+${b.ownerPhone.replace(/\D/g,'')}`, body: msg }),
      client.messages.create({ from: fromWa, to: doctorWa, body: msg }),
    ]);
    res.send({ ok: true });
  } catch (e) {
    console.error('Twilio error', e);
    res.status(500).send({ error: e.message });
  }
});

app.listen(process.env.PORT || 3000, () => {
  console.log('Booking webhook listening');
});
```

Create a `.env` file (in `server/`):
```
TWILIO_SID=ACXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
TWILIO_AUTH=your_auth_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
```

### 4. Modify Client Code

Update `js/booking.js` to POST the booking to your server before or after saving:

```javascript
function handleAppointmentSubmit(event) {
  event.preventDefault();
  // ...existing validation
  // after saveAppointment(formData);
  fetch('https://your-server.example.com/book', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(formData)
  })
  .then(r => r.json())
  .then(data => console.log('WhatsApp ping sent', data))
  .catch(err => console.error('WhatsApp error', err));
}
```

(The code already saves to localStorage and sends emails. The fetch adds WhatsApp notifications.)

### 5. Deploy the Server

You can run it on any host:
- Heroku (free tier) – just push the `server/` folder
- Vercel/Netlify using a serverless function
- A small VPS or Azure/AWS/GCP

Make sure `https://your-server.example.com` is accessible from your web page. For local testing you can use [ngrok](https://ngrok.com) to expose `http://localhost:3000`.

### 6. Template Messages (Optional)
Using free sandbox you can send plain text. For production you need WhatsApp templates:
1. In Twilio console open **Messaging > Services > Templates**
2. Create templates (e.g. `booking_confirmation`) with variables
3. Use `client.messages.create({ ..., body: 'Your booking {{1}} ...' })` or use Twilio’s API parameter `template`.

---

### Notes & Limitations

- **Privacy**: Keep Twilio credentials in server `.env`. Do not expose them client-side.
- **Sandbox limits**: You can only message numbers that have joined sandbox.
- **Costs**: WhatsApp messages cost per message when you upgrade.

---

With this setup, **both client and doctor receive WhatsApp notifications automatically** at booking time. You're ready to implement and expand as needed!
