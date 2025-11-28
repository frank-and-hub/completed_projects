const passport = require('passport');
const { Strategy: LocalStrategy } = require('passport-local');
const { Strategy: JwtStrategy, ExtractJwt } = require('passport-jwt');
const bcrypt = require('bcryptjs');
const User = require('../models/user'); // Import your User model
const secrets = require('../config/secrets');
const status_active = `${process.env.STATUS_ACTIVE}`;

// Local Strategy for login (username and password)
passport.use(new LocalStrategy({
    usernameField: 'email', 
    passwordField: 'password'
}, async (email, password, done) => {
    try {
        const user = await User.findOne({ email });
        if (!user) {
            return done(null, false, { message: 'Incorrect email' });
        }

        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) {
            return done(null, false, { message: 'Incorrect password' });
        }
        return done(null, user);
    } catch (err) {
        return done(err);
    }
}));

passport.use(new JwtStrategy({
    jwtFromRequest: ExtractJwt.fromAuthHeaderAsBearerToken(),
    secretOrKey: secrets.jwtSecret
}, async (payload, done) => {
    try {
        const user = await User.findById(payload.id).where('status').equals(status_active);
        if (!user) {
            return done(null, false);
        }

        return done(null, user);
    } catch (err) {
        return done(err, false);
    }
}));

passport.serializeUser((user, done) => {
    done(null, user.id);
});

passport.deserializeUser(async (id, done) => {
    try {
        const user = await User.findById(id).where('status').equals(status_active);
        done(null, user);
    } catch (err) {
        done(err);
    }
});

module.exports = passport;
