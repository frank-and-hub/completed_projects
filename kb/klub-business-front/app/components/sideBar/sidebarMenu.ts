// sidebarMenu.ts
export type MenuLink = {
    label: string;
    href?: string;
    icon?: string;
    children?: MenuLink[];
};

export type SidebarSection = {
    label?: string;
    items: MenuLink[];
};

export const sidebarMenu: SidebarSection[] = [
    {
        label: 'supper admin panel',
        items: [
            {
                label: 'dashboard',
                href: '/admin'
            // }, {
            //     label: 'menu management',
            //     children: [
            //         { label: 'create new menu', href: '/admin/menus/add-new' },
            //         { label: 'menu list', href: '/admin/menus/list' },
            //     ],
            }, {
                label: 'role & permission',
                children: [
                    { label: 'role', href: '/admin/roles' },
                    { label: 'permissions', href: '/admin/permissions' },
                ],
            }, {
                label: 'departments',
                children: [
                    { label: 'Departments', href: '/admin/departments' },
                ],
            }, {
                label: 'user management',
                children: [
                    { label: 'Users', href: '/admin/users' },
                    { label: 'Roles', href: '/admin/roles' },
                ]
            }, {
                label: 'business management',
                children: [
                    { label: 'Businesses', href: '/admin/business' },
                    { label: 'Employees', href: '/admin/employees' },
                    { label: 'Tasks', href: '/admin/tasks' },
                    { label: 'Events', href: '/admin/events' },
                ]
            }, {
                label: 'communication',
                children: [
                    { label: 'Chat', href: '/admin/chat' },
                    { label: 'Notifications', href: '/admin/notifications' },
                ]
            }, {
                label: 'Create Static Pages (CMS)',
                children: [
                    { label: 'Terms And Condications', href: '#' },
                    { label: 'Privacy Policy', href: '#' },
                ]
            }, {
                label: 'Business Management',
                children: [
                    { label: 'FAQ Management', href: '#' },
                    { label: 'Testimonials Management', href: '#' },
                    { label: 'Report Management', href: '#' },
                ],
            }, {
                label: 'Email System',
                children: [
                    { label: 'System announcements', href: '#' },
                    { label: 'Communication with business owners', href: '#' },
                    { label: 'Email templates for system events', href: '#' },
                ],
            }, {
                label: 'Subscription Management',
                children: [
                    { label: 'Define/manage pricing plans', href: '#' },
                    { label: 'Approve business plan upgrades', href: '#' },
                    { label: 'Subscription status & renewal tracking', href: '#' },
                ],
            }, {
                label: 'Invoice / Billing Management',
                children: [
                    { label: 'Generate invoices for business users', href: '#' },
                    { label: 'View payment logs', href: '#' },
                    { label: 'Handle billing disputes', href: '#' }
                ],
            }, {
                label: 'Admin Notifications',
                children: [
                    { label: 'System alerts', href: '#' },
                    { label: 'Email/SMS/push', href: '#' },
                ]
            }, {
                label: 'Static Categories',
                children: [
                    { label: 'Genders', href: '#' },
                    { label: 'Departments', href: '#' },
                    { label: 'Device Types', href: '#' },
                    { label: 'Duration Type', href: '#' },
                    { label: 'Business Categories', href: '#' },
                    {
                        label: 'location',
                        children: [
                            { label: 'Countries', href: '#' },
                            { label: 'States', href: '#' },
                            { label: 'City', href: '#' },
                        ],
                    },
                    {
                        label: 'Payment',
                        children: [
                            { label: 'Plan Types', href: '#' },
                            { label: 'Payment Methods', href: '#' },
                            { label: 'Currency types', href: '#' },
                        ],
                    },
                ]
            }, {
                label: 'Settings',
                children: [
                    { label: 'Theme customization', href: '#' },
                    { label: 'Contact details', href: '#' },
                    { label: 'Notifications Controle', href: '#' },
                    { label: 'Domain settings', href: '#' },
                ],
            }
        ],
    },
    {
        label: 'Business Owners',
        items: [
            {
                label: 'Dashboard',
                href: '#'
            }, {
                label: 'Profile',
                href: '#'
            }, {
                label: 'HR / Employee Management',
                children: [
                    {
                        label: 'Employee Management',
                        children: [
                            { label: 'List', href: '#' },
                            { label: 'Add New', href: '#' },
                        ],
                    },
                    { label: 'Attendance & shift scheduling', href: '#' },
                    { label: 'Payroll integration', href: '#' },
                    { label: 'Performance tracking', href: '#' },
                ],
            }, {
                label: 'Static Pages',
                children: [
                    { label: 'Terms & Conditions', href: '#' },
                    { label: 'Privacy Policy', href: '#' },
                    { label: 'Contract info', href: '#' },
                    { label: 'SEO-focused pages', href: '#' }
                ]
            }, {
                label: 'Role & Permissions Management',
                children: [
                    { label: 'Roles', href: '#' },
                    { label: 'Permissions', href: '#' },
                    { label: 'Level', href: '#' }
                ],
            }, {
                label: 'FAQ Management',
                children: [
                    { label: 'List', href: '#' },
                ],
            }, {
                label: 'Testimonials Management',
                children: [
                    { label: 'List', href: '#' },
                ],
            }, {
                label: 'Settings', // Allow timezone, currency, and language options
                href: '#'
            }, {
                label: 'Revenue Forecasting',
                children: [
                    { label: 'Visualize projected vs actual income', href: '#' },
                    { label: 'Track trends and seasonality', href: '#' },
                    { label: 'Action recommendations (optional)', href: '#' },
                ],
            }, {
                label: 'Email System',
                children: [
                    { label: 'System announcements', href: '#' },
                    { label: 'Communication with business owners', href: '#' },
                    { label: 'Email templates for system events', href: '#' },
                ],
            }, {
                label: 'Chat with Staff',
                children: [
                    { label: 'List', href: '#' },
                    { label: 'Chats', href: '#' },
                    { label: 'Groups', href: '#' },
                ]
            }, {
                label: 'Payment Management',
                children: [
                    { label: 'View all business-related transactions', href: '#' },
                    { label: 'Handle refunds', href: '#' },
                ],
            }, {
                label: 'Self Business Details',
                children: [
                    { label: 'Calender', href: '#' },
                    { label: 'Details', href: '#' },
                ],
            }, {
                label: 'Event Management',
                children: [
                    { label: 'Schedule events', href: '#' },
                    { label: 'Set reminder/notification', href: '#' }
                ],
            }, {
                label: 'Booking Management',
                children: [
                    { label: 'Define/manage pricing plans', href: '#' },
                ]
            }
        ]
    },
    {
        label: 'Employees',
        items: []
    },
    /*
    {
        label: 'Apps',
        items: [
            {
                label: 'Apps',
                icon: 'icon icon-app-store',
                children: [
                    { label: 'Profile', href: '#' },
                    {
                        label: 'Email',
                        children: [
                            { label: 'Compose', href: '#' },
                            { label: 'Inbox', href: '#' },
                            { label: 'Read', href: '#' },
                        ],
                    },
                    { label: 'Calendar', href: '#' },
                ]},
            {
                label: 'Charts',
                icon: 'icon icon-chart-bar-33',
                children: [
                    { label: 'Flot Chart', href: '#' },
                    { label: 'Morris', href: '#' },
                    { label: 'Chartjs', href: '#' },
                    { label: 'Chartist', href: '#' },
                    { label: 'Sparkline', href: '#' },
                    { label: 'Peity', href: '#' },
                ],
            },
        ],
    }, {
        label: 'Default',
        items: [
            {
                label: 'Pages',
                icon: 'icon icon-world-2',
                children: [
                    { label: 'Login', href: '/auth/sign-in' },
                    { label: 'Register', href: '/auth/sign-up' },
                    { label: 'Error 400', href: '#' },
                    { label: 'Error 403', href: '#' },
                    { label: 'Error 404', href: '#' },
                    { label: 'Error 500', href: '#' },
                    { label: 'Error 503', href: '#' },
                ],
            },
        ],
    },
    */
];
