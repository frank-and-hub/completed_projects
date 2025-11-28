// AuthServices.js
const jwt = require(`jsonwebtoken`);
const bcrypt = require(`bcrypt`);
const secrets = require(`../config/secrets`);

// Function to generate JWT token
exports.generateToken = (user) => {
    const payload = { id: user._id, email: user.email, phone: user.phone, role: user.role, name: user.name }
    return jwt.sign(payload, secrets.jwtSecret, { expiresIn: secrets.expiresin });
}

// Function to verify JWT token
exports.verifyToken = (token) => {
    return jwt.verify(token, secrets.jwtSecret);
}

// Function to hash password
exports.hashPassword = async (password) => {
    return bcrypt.hash(password, 10)
}

// Function to compare passwords (for login)
exports.comparePasswords = async (inputPassword, storedPassword) => {
    return bcrypt.compare(inputPassword, storedPassword);
}
