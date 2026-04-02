// Handle login submission
app.post('/login', (req, res) => {
    const { email, password } = req.body;

    // For now, just log the submitted data (implement authentication logic here)
    console.log('Login data:', { email, password });

    // Redirect or respond with success/failure message
    res.send('Login successful!');
});
