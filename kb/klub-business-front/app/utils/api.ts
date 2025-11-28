// API integration file for all backend endpoints
import { get, post, put, patch, destroy } from './axios';

// Auth API
export const authAPI = {
  signIn: (data: { email: string; password: string }) => post('v1/auth/sign-in', data),
  signUp: (data: any) => post('v1/auth/sign-up', data),
  forgotPassword: (email: string) => post('v1/auth/forgot-password', { email }),
  resetPassword: (data: any) => post('v1/auth/reset-password', data),
};

// Users API
export const usersAPI = {
  getAll: (params?: any) => get('v1/users', params),
  getById: (id: string) => get(`v1/users/${id}`),
  create: (data: any) => post('v1/users', data),
  update: (id: string, data: any) => put(`users/${id}`, data),
  delete: (id: string) => destroy(`v1/users/${id}`),
  getProfile: () => get('v1/users/profile'),
  getUserRole: (id: string) => get(`v1/users/${id}/role`),
  updateUserRole: (id: string, roleId: string) => patch(`v1/users/${id}/role`, { roleId }),
  getUsersByRole: (roleName: string) => get(`v1/users/role/${roleName}`),
  getUserPermissions: (id: string) => get(`v1/users/${id}/permissions`),
  assignPermission: (id: string, permissionId: string) => post(`v1/users/${id}/permissions`, { permissionId }),
  removePermission: (id: string, permissionId: string) => destroy(`v1/users/${id}/permissions/${permissionId}`),
};

// Roles API
export const rolesAPI = {
  getAll: (params?: any) => get('v1/roles', params),
  getById: (id: string) => get(`v1/roles/${id}`),
  create: (data: any) => post('v1/roles', data),
  update: (id: string, data: any) => patch(`v1/roles/${id}`, data),
  delete: (id: string) => destroy(`v1/roles/${id}`),
};

// Permissions API
export const permissionsAPI = {
  getAll: (params?: any) => get('v1/permission', params),
  getById: (id: string) => get(`v1/permission/${id}`),
  create: (data: any) => post('v1/permission', data),
  update: (id: string, data: any) => patch(`v1/permission/${id}`, data),
  delete: (id: string) => destroy(`v1/permission/${id}`),
};

// Business API
export const businessAPI = {
  getAll: (params?: any) => get('v1/business', params),
  getById: (id: string) => get(`v1/business/${id}`),
  create: (data: any) => post('v1/business', data),
  update: (id: string, data: any) => patch(`v1/business/${id}`, data),
  delete: (id: string) => destroy(`v1/business/${id}`),
  getUserBusinesses: (userId: string) => get(`v1/business/user/${userId}`),
  getOwners: (id: string) => get(`v1/business/${id}/owners`),
  getEmployees: (id: string) => get(`v1/business/${id}/employees`),
  addOwner: (id: string, userId: string) => post(`v1/business/${id}/owners`, { userId }),
  removeOwner: (id: string, userId: string) => destroy(`v1/business/${id}/owners/${userId}`),
};

// Employee API
export const employeeAPI = {
  getAll: (params?: any) => get('v1/employee', params),
  getById: (id: string) => get(`v1/employee/${id}`),
  create: (data: any) => post('v1/employee', data),
  update: (id: string, data: any) => patch(`v1/employee/${id}`, data),
  delete: (id: string) => destroy(`v1/employee/${id}`),
  getByUserId: (userId: string) => get(`v1/employee?userId=${userId}`),
  getByDepartment: (departmentId: string) => get(`v1/employee?departmentId=${departmentId}`),
  getAttendance: (id: string, startDate?: string, endDate?: string) => {
    const params = new URLSearchParams();
    if (startDate) params.append('startDate', startDate);
    if (endDate) params.append('endDate', endDate);
    return get(`v1/employee/${id}/attendance?${params.toString()}`);
  },
  getSalaryRecords: (id: string) => get(`v1/employee/${id}/salary`),
  getPerformanceLogs: (id: string) => get(`v1/employee/${id}/performance`),
};

// Task API
export const taskAPI = {
  getAll: (params?: any) => get('v1/task', params),
  getById: (id: string) => get(`v1/task/${id}`),
  create: (data: any) => post('v1/task', data),
  update: (id: string, data: any) => patch(`v1/task/${id}`, data),
  delete: (id: string) => destroy(`v1/task/${id}`),
  getByAssignedTo: (assignedTo: string) => get(`v1/task?assignedTo=${assignedTo}`),
  getByAssignedBy: (assignedBy: string) => get(`v1/task?assignedBy=${assignedBy}`),
  getByBusiness: (businessId: string) => get(`v1/task?businessId=${businessId}`),
  getOverdue: () => get('v1/task?overdue=true'),
  getStats: (userId: string) => get(`v1/task/stats?userId=${userId}`),
  markAsCompleted: (id: string, data: any) => patch(`v1/task/${id}/complete`, data),
};

// Event API
export const eventAPI = {
  getAll: (params?: any) => get('v1/event', params),
  getById: (id: string) => get(`v1/event/${id}`),
  create: (data: any) => post('v1/event', data),
  update: (id: string, data: any) => patch(`v1/event/${id}`, data),
  delete: (id: string) => destroy(`v1/event/${id}`),
  getByBusiness: (businessId: string) => get(`v1/event?businessId=${businessId}`),
  getUpcoming: () => get('v1/event?upcoming=true'),
  getAttendees: (id: string) => get(`v1/event/${id}/attendees`),
  attendEvent: (id: string, userId: string) => post(`v1/event/${id}/attend`, { userId }),
  cancelAttendance: (id: string, userId: string) => post(`v1/event/${id}/cancel-attendance`, { userId }),
};

// Chat API
export const chatAPI = {
  getAll: () => get('v1/chat'),
  getById: (id: string) => get(`v1/chat/${id}`),
  create: (data: any) => post('v1/chat', data),
  update: (id: string, data: any) => patch(`v1/chat/${id}`, data),
  delete: (id: string) => destroy(`v1/chat/${id}`),
  getMessages: (id: string) => get(`v1/chat/${id}/messages`),
  sendMessage: (id: string, data: any) => post(`v1/chat/${id}/messages`, data),
  markAsRead: (id: string, data: any) => patch(`v1/chat/${id}/read`, data),
};

// Notification API
export const notificationAPI = {
  getAll: (params?: any) => get('v1/notification', params),
  getById: (id: string) => get(`v1/notification/${id}`),
  create: (data: any) => post('v1/notification', data),
  update: (id: string, data: any) => patch(`v1/notification/${id}`, data),
  delete: (id: string) => destroy(`v1/notification/${id}`),
  markAsRead: (id: string, data: any) => patch(`v1/notification/${id}/read`, data),
  markAllAsRead: (data: any) => patch('v1/notification/mark-all-read', data),
  getUnread: () => get('v1/notification?unread=true'),
};

// Department API
export const departmentAPI = {
  getAll: (params?: any) => get('v1/departments', params),
  getById: (id: string) => get(`v1/departments/${id}`),
  create: (data: any) => post('v1/departments', data),
  update: (id: string, data: any) => patch(`v1/departments/${id}`, data),
  delete: (id: string) => destroy(`v1/departments/${id}`),
};

// File API
export const fileAPI = {
  upload: (file: File) => {
    const formData = new FormData();
    formData.append('file', file);
    return post('v1/files/upload', formData);
  },
  getById: (id: string) => get(`v1/files/${id}`),
  delete: (id: string) => destroy(`v1/files/${id}`),
  getByUser: (userId: string) => get(`v1/files?userId=${userId}`),
};

// Business Category API
export const businessCategoryAPI = {
  getAll: (params?: any) => get('v1/business-category', params),
  getById: (id: string) => get(`v1/business-category/${id}`),
  create: (data: any) => post('v1/business-category', data),
  update: (id: string, data: any) => patch(`v1/business-category/${id}`, data),
  delete: (id: string) => destroy(`v1/business-category/${id}`),
};

// Plans API
export const plansAPI = {
  getAll: (params?: any) => get('v1/plans', params),
  getById: (id: string) => get(`v1/plans/${id}`),
  create: (data: any) => post('v1/plans', data),
  update: (id: string, data: any) => patch(`v1/plans/${id}`, data),
  delete: (id: string) => destroy(`v1/plans/${id}`),
};

// Currency API
export const currencyAPI = {
  getAll: (params?: any) => get('v1/currency', params),
  getById: (id: string) => get(`v1/currency/${id}`),
  create: (data: any) => post('v1/currency', data),
  update: (id: string, data: any) => patch(`v1/currency/${id}`, data),
  delete: (id: string) => destroy(`v1/currency/${id}`),
};

// Countries API
export const countriesAPI = {
  getAll: (params?: any) => get('v1/countries', params),
  getById: (id: string) => get(`v1/countries/${id}`),
};

// States API
export const statesAPI = {
  getAll: (params?: any) => get('v1/states', params),
  getById: (id: string) => get(`v1/states/${id}`),
  getByCountry: (countryId: string) => get(`v1/states?countryId=${countryId}`),
};

// Cities API
export const citiesAPI = {
  getAll: (params?: any) => get('v1/cities', params),
  getById: (id: string) => get(`v1/cities/${id}`),
  getByState: (stateId: string) => get(`v1/cities?stateId=${stateId}`),
  getByCountry: (countryId: string) => get(`v1/cities?countryId=${countryId}`),
};

// Menu API
export const menuAPI = {
  getAll: (params?: any) => get('v1/menu', params),
  getById: (id: string) => get(`v1/menu/${id}`),
  create: (data: any) => post('v1/menu', data),
  update: (id: string, data: any) => patch(`v1/menu/${id}`, data),
  delete: (id: string) => destroy(`v1/menu/${id}`),
};

// Common Data API
export const commonDataAPI = {
  getAll: (params?: any) => get('v1/common-data', params),
  getById: (id: string) => get(`v1/common-data/${id}`),
  create: (data: any) => post('v1/common-data', data),
  update: (id: string, data: any) => patch(`v1/common-data/${id}`, data),
  delete: (id: string) => destroy(`v1/common-data/${id}`),
};

// Verification API
export const verificationAPI = {
  getAll: (params?: any) => get('v1/verification', params),
  getById: (id: string) => get(`v1/verification/${id}`),
  create: (data: any) => post('v1/verification', data),
  update: (id: string, data: any) => patch(`v1/verification/${id}`, data),
  delete: (id: string) => destroy(`v1/verification/${id}`),
  verify: (id: string, data: any) => patch(`v1/verification/${id}/verify`, data),
  reject: (id: string, data: any) => patch(`v1/verification/${id}/reject`, data),
};

// Log API
export const logAPI = {
  getAll: (params?: any) => get('v1/log', params),
  getById: (id: string) => get(`v1/log/${id}`),
  getByUser: (userId: string) => get(`v1/log?userId=${userId}`),
  getByAction: (action: string) => get(`v1/log?action=${action}`),
};

// Business Static Page API
export const businessStaticPageAPI = {
  getAll: (params?: any) => get('v1/business-static-page', params),
  getById: (id: string) => get(`v1/business-static-page/${id}`),
  create: (data: any) => post('v1/business-static-page', data),
  update: (id: string, data: any) => patch(`v1/business-static-page/${id}`, data),
  delete: (id: string) => destroy(`v1/business-static-page/${id}`),
};
