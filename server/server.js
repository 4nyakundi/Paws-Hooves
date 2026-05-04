require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const twilio = require('twilio');

const app = express();
app.use(cors());
app.use(bodyParser.json());

const client = twilio(process.env.TWILIO_SID, process.env.TWILIO_AUTH);
const fromWa = process.env.TWILIO_WHATSAPP_FROM; 
const doctorWa = 'whatsapp:+254759396531';

app.post('/book', async (req, res) => {
  const b = req.body;
  
  const msg = `Paws & Hooves Booking:\nID: ${b.bookingId}\nName: ${b.ownerName}\nPet: ${b.petName} (${b.petType})\nDate: ${b.appointmentDate} at ${b.appointmentTime}\nService: ${b.appointmentType}\nNotes: ${b.appointmentNotes || 'None'}`;

  try {
    const messages = [];
    
    // Send to doctor
    messages.push(client.messages.create({ from: fromWa, to: doctorWa, body: msg }));
    
    // Send to client
    if (b.ownerPhone) {
        // Ensure phone number starts with country code, default to Kenya (+254) if it starts with 0
        let clientPhone = b.ownerPhone.replace(/\D/g, '');
        if (clientPhone.startsWith('0')) {
            clientPhone = '254' + clientPhone.substring(1);
        } else if (!clientPhone.startsWith('254')) {
            // Very basic fallback if they didn't include code
            clientPhone = '254' + clientPhone;
        }
        
        messages.push(client.messages.create({ 
            from: fromWa, 
            to: `whatsapp:+${clientPhone}`, 
            body: `Hi ${b.ownerName}, your booking at Paws & Hooves is confirmed!\n\n${msg}` 
        }));
    }

    await Promise.all(messages);
    res.send({ ok: true });
  } catch (e) {
    console.error('Twilio error', e);
    res.status(500).send({ error: e.message });
  }
});

app.listen(process.env.PORT || 3000, () => {
  console.log('Booking webhook listening on port ' + (process.env.PORT || 3000));
});
