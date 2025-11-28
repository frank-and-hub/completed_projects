const mongoose = require('mongoose');
const axios = require('axios');
const User = require('../models/user');
const Role = require('../models/role');
const Menu = require('../models/menu');
const Country = require('../models/country');
const Permission = require('../models/permission');
const { faker } = require('@faker-js/faker');
const connectDB = require('../config/db');
const AuthServices = require('../services/AuthServices');
const { getGuestRole } = require('../controllers/RoleController');
const { makeSlug } = require('../utils/helper');
const Warranty = require('../models/warranty');
const Warehouse = require('../models/warehouse');
const Unit = require('../models/unit');
// const Store = require('../models/store');
// const SubCategory = require('../models/sub_category');

let i = 0;
const generateFakeData = async (fakePassword, guestRoleId) => {
  const basePhoneNumber = 9999990000;
  const baseZipCodeNumber = 99999;
  i++;
  const phoneNumber = basePhoneNumber + i;
  return {
    _id: new mongoose.Types.ObjectId(),
    name: {
      first_name: faker.person.firstName(),
      middle_name: faker.person.firstName(),
      last_name: faker.person.lastName(),
    },
    dial_code: generateDialCode(),
    phone: phoneNumber,
    email: faker.internet.email(),
    password: await AuthServices.hashPassword(fakePassword),
    password_text: fakePassword,
    role: await guestRoleId,
    status: true,
    image: null,
    gender: faker.person.sex(),
    address: faker.location.streetAddress(),
    about: faker.lorem.sentence(),
    city: faker.location.city(),
    state: faker.location.state(),
    zipcode: baseZipCodeNumber + i,
    terms: true,
    updated_by: null,
    deleted_at: null
  }
};

const generateDialCode = () => {
  const countryCodes = ['1', '44', '91', '61', '49', '33', '81', '55', '27', '91', '59', '94'];
  const randomCode = countryCodes[Math.floor(Math.random() * countryCodes.length)];
  return `+${randomCode}`;
};

const defaultRoleData = [
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'supper-admin',
    permissions: [],
    status: true,
    updated_by: null,
    deleted_at: null
  }, {
    _id: new mongoose.Types.ObjectId(),
    name: 'admin',
    permissions: [],
    status: true,
    updated_by: null,
    deleted_at: null
  }, {
    _id: new mongoose.Types.ObjectId(),
    name: 'customer',
    permissions: [],
    status: true,
    updated_by: null,
    deleted_at: null
  }, {
    _id: new mongoose.Types.ObjectId(),
    name: 'supplier',
    permissions: [],
    status: true,
    updated_by: null,
    deleted_at: null
  }, {
    _id: new mongoose.Types.ObjectId(),
    name: 'employee',
    permissions: [],
    status: true,
    updated_by: null,
    deleted_at: null
  },
];

const menuSeederData = [
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'dashboard',
    route: '/admin',
    type: true,
    icon: 'bi bi-grid',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'user',
    route: '#',
    type: true,
    icon: 'bi bi-person-circle',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'users list',
    route: 'users',
    type: true,
    icon: 'bi bi-people-fill',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'users permissions',
    route: 'users/permissions',
    type: true,
    icon: 'bi bi-person-square',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'menu',
    route: '#',
    type: true,
    icon: 'bi bi-card-list',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'menu list',
    route: 'menus',
    type: true,
    icon: 'bi bi-view-list',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'role and permission',
    route: 'menus/role-and-permission',
    type: true,
    icon: 'bi bi-file-lock-fill',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'profile',
    route: 'profile',
    type: false,
    icon: 'bi bi-person-bounding-box',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'role',
    route: 'roles',
    type: true,
    icon: 'bi bi-person-badge',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'social media',
    route: 'settings/social-details',
    type: true,
    icon: 'bi bi-phone',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'banner',
    route: 'banners',
    type: true,
    icon: 'bi bi-card-image',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'brand',
    route: 'brands',
    type: true,
    icon: 'bi bi-briefcase-fill',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'payment',
    route: 'payments',
    type: true,
    icon: 'bi bi-paypal',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'product',
    route: '#',
    type: true,
    icon: 'bi bi-box',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'discount',
    route: 'products/discounts',
    type: true,
    icon: 'bi bi-ticket-detailed',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'categories',
    route: 'products/categories',
    type: true,
    icon: 'bi bi-dropbox',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'sub category',
    route: 'products/sub-categories',
    type: true,
    icon: 'bi bi-inbox-fill',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'tag',
    route: 'products/tags',
    type: true,
    icon: 'bi bi-tags-fill',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'color',
    route: 'products/colors',
    type: true,
    icon: 'bi bi-droplet-half',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'product list',
    route: 'products',
    type: true,
    icon: 'bi bi-boxes',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'setting',
    route: '#',
    type: true,
    icon: 'bi bi-gear-wide-connected',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'support',
    route: 'settings/support',
    type: true,
    icon: 'bi bi-headphones',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'terms and conditions',
    route: 'settings/terms-and-conditions',
    type: true,
    icon: 'bi bi-file-earmark-medical-fill',
    parent: null,
    status: true,
    deleted_at: null
  },
  {
    _id: new mongoose.Types.ObjectId(),
    name: 'frequently asked questions',
    route: 'settings/faqs',
    type: true,
    icon: 'bi bi-exclamation-circle-fill',
    parent: null,
    status: true,
    deleted_at: null
  }
];

const seedUserData = async () => {
  try {
    // Connect to the database
    await connectDB();

    console.log('Database connected');
    const fakeData = [];
    const guestRoleId = getGuestRole('guest-user');

    await Role.deleteMany();
    await Role.insertMany(defaultRoleData);

    for (let i = 0; i < 50; i++) {
      const fakePassword = faker.string.alphanumeric(16);
      fakeData.push(await generateFakeData(fakePassword, guestRoleId));
    }

    // Insert user data
    await User.deleteMany();
    await User.insertMany(fakeData);
    await Menu.deleteMany();
    for (const data of menuSeederData) {
      // Check for existing menu
      const existingMenu = await Menu.findOne({ name: data.name });
      if (!existingMenu) {
        const newMenu = await Menu.create(data);
        console.log(`Inserted default menu: ${newMenu.name}`);
        // Seed permissions for the menu if route is `/`
        if (newMenu.route === '/') {
          const permissionNames = ['delete', 'read', 'update', 'create'];
          await Promise.all(permissionNames.map(async (permName) => {
            const permission = new Permission({
              _id: new mongoose.Types.ObjectId(),
              name: `${permName} - ${newMenu.name}`,
              menu: newMenu._id
            });
            await permission.save();
            console.log(`Permission created: ${permission.name}`);
          }));
        }
      }
    }
    console.log('data seeded successfully');
  } catch (err) {
    console.error('Error seeding data:', err);
    mongoose.disconnect();
  } finally {
    console.log('Database disconnected');
  }
};

const fetchAndStoreCountries = async () => {
  try {
    await connectDB();
    console.log('Database connected');

    const response = await axios.get('https://restcountries.com/v3.1/all');
    const countriesData = response.data;

    const countryDocuments = countriesData.map((country) => ({
      _id: new mongoose.Types.ObjectId(),
      name: country.name.common,
      dialCode: `${country.idd.root}${country.idd.suffixes ? country.idd.suffixes[0] : ''}`,
      region: country.region,
      subregion: country.subregion,
      population: country.population,
      capital: country.capital,
      flags: country.flags,
    }));

    await Country.insertMany(countryDocuments);
    console.log('Countries data saved to MongoDB');
  } catch (err) {
    console.error('Error seeding data:', err);
    mongoose.disconnect();
  } finally {
    console.log('Database disconnected');
  }
}
const updateSlug = async () => {
  try {
    await connectDB();
    const entities = [
      // { model: User, field: (doc) => `${doc.name?.first_name}-${doc.name?.middle_name ?? ''}-${doc.name?.last_name ?? ''}` },
      { model: Warranty, field: (doc) => doc.name },
      { model: Warehouse, field: (doc) => doc.name },
      { model: Unit, field: (doc) => doc.name },
      // { model: Store, field: (doc) => doc.name },
      // { model: SubCategory, field: (doc) => doc.name },
      // { model: Permission, field: (doc) => doc.name },
    ];

    for (const { model, field } of entities) {
      const docs = await model.find();
      const updates = docs.map((doc) => {
        doc.slug = makeSlug(field(doc));
        if(doc.updated_by){
          doc.updated_by = `67cd90e6dae949bb24ad8af2`;
        }
        if (doc.user){
          doc.user = `67cd90e6dae949bb24ad8af2`;
        } 
        return doc.save();
      });

      await Promise.all(updates); // parallel save
      console.log(`Updated slugs for ${model.modelName}: ${updates.length} items`);
    }

   } catch (err) {
    console.error('Error seeding data:', err);
    mongoose.disconnect();
  } finally {
    console.log('Database disconnected');
  }
};

// Run the Seeder
// seedUserData();
// fetchAndStoreCountries();
updateSlug();