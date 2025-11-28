module.exports = (req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Max-Age', '3600');
    res.header('Content-Type', 'application/json');
    // res.header('Accept', 'application/json');
    res.header('Access-Control-Allow-Headders', 'Origin, X-Requested-With,Contect-Type, Accept, Authorization');
    if (req.method === 'OPTIONS') {
        res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE');
        return res.status(200).json({});
    }
    next()
}