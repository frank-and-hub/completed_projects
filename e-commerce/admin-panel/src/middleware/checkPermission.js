const User = require('../models/user');
const Role = require('../models/role');
const Permission = require('../models/permission');
const Menu = require('../models/menu');

const checkPermission = (menuName, permissionName) => {
    return async (req, res, next) => {
        try {
            // const userId = req?.userData?.id;
            // const user = await User.findById(userId).select('_id role').where('status').equals(process.env.STATUS_ACTIVE).populate({ path: 'role', select: '_id name permissions', populate: { path: 'permissions', select: '_id name menu', populate: { path: 'menu', select: '_id name' } } });
            // console.log(user);
            // if (!user || !user.role) return res.status(403).json({ message: `User doesn't have a role` });
            // console.log(user.role.permissions);
            // if (!user.role.permissions || user.role.permissions.length === 0) return res.status(403).json({ message: 'No permissions assigned to the role' });

            /*
            const permissionForMenu = user.role.permissions.find(perm =>
                perm.menu?.name === menuName && perm.name === permissionName
            );

            if (!permissionForMenu) return res.status(403).json({ message: `Permission denied for ${permissionName} on ${menuName}` });
            */
           
            next()
        } catch (err) {
            next(err)
        }
    }
}

module.exports = checkPermission;
