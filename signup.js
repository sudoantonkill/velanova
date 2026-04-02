// server.js
const express = require('express');
const bodyParser = require('body-parser');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));

// Serve static files (HTML, CSS)
app.use(express.static(path.join(__dirname, 'public')));

// Handle form submission
app.post('/register', (req, res) => {
    const { username, email, password } = req.body;

    // For now, just log the submitted data (implement database logic here)
    console.log('User data:', { username, email, password });

    // Redirect or respond with success message
    res.send('Registration successful!');
});

// Start server
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
