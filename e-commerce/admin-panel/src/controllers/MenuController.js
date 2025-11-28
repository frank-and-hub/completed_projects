const mongoose = require('mongoose');
// models
const Menu = require('../models/menu');
const Permission = require('../models/permission');
// helper function
const helper = require('../utils/helper');

// config url
const url = require('../config/url');;

// base url
const baseurl = `${url.apiUrl}`;
// status
const status_active = `${process.env.STATUS_ACTIVE}`;
const data_limit = `${process.env.DATA_PAGINATION_LIMIT}`;
// custom content-name
const constName = 'menus/';

const fetchSubMenues = async (menuId) => {
    const subMenu = await Menu.find({ parent: menuId }).select('_id name route type parent status');
    if (subMenu.length === 0) return [];

    const subMenuPromises = subMenu.map(async (child) => {
        const subMenues = await fetchSubMenues(child._id);
        return {
            ...child.toObject(),
            sub_menu: subMenues
        }
    });
    return await Promise.all(subMenuPromises);
}

const buildMenuTree = (menuList, parentId = null) => {
    return menuList
        .filter(menu => String(menu.parent) === String(parentId))
        .map(menu => ({
            id: menu._id,
            name: menu.name,
            route: menu.route,
            icon: menu.icon,
            status: menu.status,
            slug: menu.slug,
            sub_menu: buildMenuTree(menuList, menu._id),
            request: parentId === null ? {
                method: 'GET',
                url: `${baseurl}${constName}${menu._id}`
            } : undefined
        }));
};

exports.index = async (req, res, next) => {
    const { filter_type, search } = req.query;
    try {
        const page = parseInt(req?.query?.page) || 1;
        const limit = parseInt(req?.query?.limit) || parseInt(data_limit);
        const skip = (page - 1) * limit;

        const orderByField = req?.query?.orderby || '_id';
        const orderByDirection = req?.query?.direction === 'asc' ? 1 : -1;

        const filter = { deleted_at: null };
        if (!filter_type) filter.parent = null;

        if (search) {
            const trimmedSearch = search.trim();
            filter.$or = [
                { name: { $regex: trimmedSearch, $options: "i" } },
                { slug: { $regex: trimmedSearch, $options: "i" } },
            ];
        }

        const query = Menu.find(filter)
            .select('_id name route type parent status icon updated_by status slug')
            .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

        if (filter_type) {
            query.sort({ [orderByField]: orderByDirection })
                .skip(skip)
                .limit(limit);
        }

        const [menus, totalCount] = await Promise.all([
            query,
            Menu.countDocuments({ ...filter })
        ]);

        if (menus.length === 0) return res.status(200).json({ message: `No menus found`, data: [] });

        let responseData;

        if (!filter_type) {
            // Fetch all menus once to build tree
            const allMenus = await Menu.find({ deleted_at: null }).select('_id name route type parent status icon slug');
            responseData = buildMenuTree(allMenus);
        } else {
            responseData = menus.map(menu => ({
                id: menu._id,
                name: menu.name,
                route: menu.route,
                icon: menu.icon,
                status: menu.status,
                slug: menu.slug
            }));
        }

        res.status(200).json({
            message: `List data successfully`, response: {
                count: totalCount,
                page: page,
                limit: limit,
                totalPages: Math.ceil(totalCount / limit),
                data: responseData
            }, title: 'Menu'
        });

    } catch (err) { next(err) }
}

exports.create = (req, res, next) => {
    try {
        res.status(200).json({
            message: `Create menu form`,
            body: {
                'name': 'String',
                'route': 'String',
                'type': 'Boolean',
                'icon': 'String',
                'parent': 'schemaId|optional'
            },
            title: 'Add menu'
        });
    } catch (err) { next(err) }
}

exports.store = async (req, res, next) => {
    try {
        const { name, route, type, icon, parent: parentId } = req.body;
        let parent = null;

        if (parentId != '') {
            parent = await Menu.findById(parentId).select('_id').where('status').equals(status_active);
            if (!parent) return res.status(404).json({ message: `Parent menu not found` });
        }

        const existingMenu = await Menu.findOne({ name: name });
        if (existingMenu) return res.status(409).json({ message: `Menu already exists` });

        const menu = new Menu({
            _id: new mongoose.Types.ObjectId(),
            name,
            route,
            icon,
            type,
            parent: parentId || null,
        });

        const newData = await menu.save();

        if (route == '/') {
            const permissionNames = ['delete', 'read', 'update', 'create'];
            await Promise.all(permissionNames.map(async (permName) => {
                const permission = new Permission({
                    _id: new mongoose.Types.ObjectId(),
                    name: `${permName} - ${newData.name}`,
                    menu: newData._id
                });
                await permission.save();
            }));
        }
        const response = {
            'id': newData._id,
            'name': newData.name,
            'route': newData.route,
            'icon': newData.icon,
            'type': newData.type,
            'parent': parent?.name
        }
        res.status(201).json({ message: `Successfully new menu created`, data: response });
    } catch (err) { next(err) }
}

exports.show = async (req, res, next) => {
    const { id } = req.params;
    try {
        const menuData = await this.findData(id, res);
        const { _id, name, route, icon, type, updated_by, parent, status } = menuData;
        const children = await Menu.find({ parent: _id }).select('_id name route');

        const result = {
            'id': _id,
            'name': name,
            'route': route,
            'icon': icon,
            'type': type,
            'child': children,
            'parent': parent,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Menu data found`, data: result, title: `View ${name} menu detail` });
    } catch (err) { next(err) }
}

exports.edit = async (req, res, next) => {
    const { id } = req.params;
    try {
        const menuData = await this.findData(id, res);
        const { _id, name, route, icon, type, updated_by, parent, status } = menuData;
        const children = await Menu.find({ parent: _id }).select('_id name route');
        const result = {
            'id': _id,
            'name': name,
            'route': route,
            'icon': icon,
            'type': type,
            'child': children,
            'parent': parent,
            'status': status,
            'updated_by': updated_by
        }
        res.status(200).json({ message: `Menu data found`, data: result, title: `Edit ${name} menu detail` });
    } catch (err) { next(err) }
}

exports.update = async (req, res, next) => {
    const { id } = req.params;
    try {
        const menu = await this.findData(id, res);
        if (!menu) return res.status(404).json({ message: `Menu not found!`, });

        if (!Array.isArray(req.body)) return res.status(400).json({ message: `No details were updated (menu may not exist or the data is the same)` });

        const updateOps = helper.updateOps(req);

        if (updateOps['parent'] && updateOps['parent'] !== '' && updateOps['parent'] !== null) {
            const parent = await Menu.findById(updateOps['parent']).select('_id').where('status').equals(status_active);
            if (!parent) return res.status(404).json({ message: `Parent menu not found` });
        } else {
            updateOps['parent'] = null;
        }

        const result = await Menu.updateOne({ _id: menu._id }, { $set: updateOps });
        if (result.modifiedCount > 0) {
            const updatedMenu = await this.findData(id, res);
            const { _id, name, route, parent, icon, type } = updatedMenu;
            const response = {
                'id': _id,
                'name': name,
                'route': route,
                'icon': icon,
                'type': type,
                'parent': parent
            }
            return res.status(200).json({ message: `Menu details updated successfully`, data: response });
        }
        res.status(404).json({ message: `Menu not found or no details to update`, data: [] });
    } catch (err) { next(err) }
}

exports.destroy = async (req, res, next) => {
    const { id } = req.params;
    try {
        const getMenu = await Menu.findById(id).select('_id').where('status').equals(!status_active);
        if (!getMenu) return res.status(404).json({ message: 'Menu not found' });

        const permissionData = await Permission.deleteMany({ menu: id });

        // const menuData = await Menu.deleteOne({ _id: id });
        // if (menuData.deletedCount === 1) {

        const menuData = await Menu.findByIdAndUpdate(id, { deleted_at: new Date() });
        if (menuData) {
            const response = {
                'method': 'POST',
                'url': `${baseurl}${constName}`,
                'body': {
                    'name': 'String',
                    'route': 'String',
                    'icon': 'String',
                    'type': 'Bolean',
                    'parent': 'nullable|ID',
                }
            }
            return res.status(200).json({ message: `Deleted successfully`, request: response });
        }
        res.status(404).json({ message: `Menu not found` });
    } catch (err) { next(err) }
}

exports.assignMenu = async (req, res, next) => {
    const { id } = req.params;
    const { parent: parentId } = req.body;
    try {
        const menu = await this.findData(id, res);
        if (!menu) return res.status(404).json({ message: `Menu not found` });

        if (parentId) {
            const parentMenu = await Menu.findById(parentId).select('_id').where('status').equals(status_active);
            if (!parentMenu) return res.status(404).json({ message: `Parent menu not found` });
        }
        const result = await Menu.updateOne({ _id: menu._id }, { $set: { parent: parentId || null } });

        if (result.modifiedCount > 0) return res.status(200).json({ message: `Menu details updated successfully` });

        res.status(400).json({ message: `No details updated successfully or menu not found` });
    } catch (err) { next(err) }
}

exports.findData = async (identifier, res) => {
    let query = (mongoose.Types.ObjectId.isValid(identifier)) ? Menu.findById(identifier): Menu.findOne({ slug: identifier });
    
    const menuData = await query
        .select('_id name route parent icon type updated_by status slug')
        .populate('updated_by', '_id name.first_name name.middle_name name.last_name');

    if (!menuData) return res.status(404).json({ message: `Menu not found` });
    return menuData;
}