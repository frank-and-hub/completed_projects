import { PrismaClient } from "@prisma/client";

const MenuData = [
  {
    name: "dashboard",
    // route: "/admin",
    // type: true,
    // icon: "bi bi-grid",
    slug: "dashboard"
  },
  {
    name: "user",
    // route: "#",
    // type: true,
    // icon: "bi bi-person-circle",
    slug: "user"
  },
  {
    name: "menu",
    // route: "#",
    // type: true,
    // icon: "bi bi-card-list",
    slug: "menu"
  },
  {
    name: "menu list",
    // route: "menus",
    // type: true,
    // icon: "bi bi-view-list",
    slug: "menu-list"
  },
  {
    name: "role and permission",
    // route: "menus/role-and-permission",
    // type: true,
    // icon: "bi bi-file-lock-fill",
    slug: "role-and-permission"
  },
  {
    name: "users list",
    // route: "users",
    // type: true,
    // icon: "bi bi-people-fill",
    slug: "users-list"
  },
  {
    name: "users permissions",
    // route: "users/permissions",
    // type: true,
    // icon: "bi bi-person-square",
    slug: "users-permissions"
  },
  {
    name: "role",
    // route: "roles",
    // type: true,
    // icon: "bi bi-person-badge",
    slug: "role"
  },
  {
    name: "social media",
    // route: "settings/social-details",
    // type: true,
    // icon: "bi bi-phone",
    slug: "social-media"
  },
  {
    name: "banner",
    // route: "banners",
    // type: true,
    // icon: "bi bi-card-image",
    slug: "banner"
  },
  {
    name: "brand",
    // route: "brands",
    // type: true,
    // icon: "bi bi-briefcase-fill",
    slug: "brand"
  },
  {
    name: "payment",
    // route: "payments",
    // type: true,
    // icon: "bi bi-paypal",
    slug: "payment"
  },
  {
    name: "product",
    // route: "#",
    // type: true,
    // icon: "bi bi-box",
    slug: "product"
  },
  {
    name: "discount",
    // route: "products/discounts",
    // type: true,
    // icon: "bi bi-ticket-detailed",
    slug: "discount"
  },
  {
    name: "categories",
    // route: "products/categories",
    // type: true,
    // icon: "bi bi-dropbox",
    slug: "categories"
  },
  {
    name: "sub category",
    // route: "products/sub-categories",
    // type: true,
    // icon: "bi bi-inbox-fill",
    slug: "sub-category"
  },
  {
    name: "tag",
    // route: "products/tags",
    // type: true,
    // icon: "bi bi-tags-fill",
    slug: "tag"
  },
  {
    name: "color",
    // route: "products/colors",
    // type: true,
    // icon: "bi bi-droplet-half",
    slug: "color"
  },
  {
    name: "product list",
    // route: "products",
    // type: true,
    // icon: "bi bi-boxes",
    slug: "product-list"
  },
  {
    name: "setting",
    // route: "#",
    // type: true,
    // icon: "bi bi-gear-wide-connected",
    slug: "setting"
  },
  {
    name: "terms and conditions",
    // route: "pages/terms-and-conditions",
    // type: true,
    // icon: "bi bi-file-earmark-medical-fill",
    slug: "terms-and-conditions"
  },
  {
    name: "support",
    // route: "supports",
    // type: true,
    // icon: "bi bi-headphones",
    slug: "support"
  },
  {
    name: "frequently asked questions",
    // route: "settings/faqs",
    // type: true,
    // icon: "bi bi-question-octagon",
    slug: "frequently-asked-questions"
  },
  {
    name: "pages",
    // route: "#",
    // type: true,
    // icon: "bi bi-list-columns-reverse",
    slug: "pages"
  },
  {
    name: "about",
    // route: "pages/about-us",
    // type: true,
    // icon: "bi bi-info-circle",
    slug: "about"
  },
  {
    name: "return and refund policy",
    // route: "pages/return-and-refund-policies",
    // type: true,
    // icon: "bi bi-arrow-return-left",
    slug: "return-and-refund-policy"
  },
  {
    name: "units",
    // route: "products/units",
    // type: true,
    // icon: "bi bi-projector",
    slug: "units"
  },
  {
    name: "warranty",
    // route: "warranties",
    // type: true,
    // icon: "bi bi-paperclip",
    slug: "warranty"
  },
  {
    name: "storage",
    // route: "#",
    // type: true,
    // icon: "bi bi-box-seam",
    slug: "storage"
  },
  {
    name: "store",
    // route: "storage/stores",
    // type: true,
    // icon: "bi bi-shop",
    slug: "store"
  },
  {
    name: "warehouse",
    // route: "storage/warehouses",
    // type: true,
    // icon: "bi bi-boxes",
    slug: "warehouse"
  },
  {
    name: "error",
    // route: "menus/errors/list",
    // type: true,
    // icon: "bi bi-bug-fill",
    slug: "error"
  },
  {
    name: "department",
    // route: "departments",
    // type: true,
    // icon: "bi bi-person-check-fill",
    slug: "department"
  }
];

const prisma = new PrismaClient();

export async function createMenu() {
    await prisma.menu.deleteMany();
    await prisma.menu.createMany({
        data: MenuData,
        skipDuplicates: true,
    });

    console.log('Seeded menus');
}
