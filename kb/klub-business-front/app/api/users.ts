"use client";

import { get, patch, post } from "@/utils/axios";
import { CommonQueryParams } from "@/types/CommonQueryParams";
import { userDetails } from "@/types/User";
import { cc } from '@/utils/console';
import { sanitizePatchData } from "@/utils/helpers";

export const getUserList = async (params: CommonQueryParams) => {
  const searchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      searchParams.set(key, String(value));
    }
  });

  const url = `v1/users?${searchParams.toString()}`;
  cc("Request URL:", url);

  return await get(url);
}

export const createNewUser = async (userData: userDetails) => {
  const url = `v1/users`;
  cc("Creating new user with data:", userData);
  return await post(url, userData);
}

export const getUserDetails = async (id: string) => {
  const url = `v1/users/${id}`;
  return await get(url);
}

export const updateDetails = async (data: any, id: string) => {
  if (!id) throw Error("ID is required to update user");
  const allowedFields = [
  'firstName',
  'middleName',
  'lastName',
  'email',
  'phone',
  'dateOfBirth',
  'gender',
  'relationshipStatus',
  'deviceType',
  'roleId',
  'status',
  'isNotify',
];
const sanitizedData = sanitizePatchData(data, allowedFields);
  const url = `v1/users/${id}`;
  return await patch(url, sanitizedData);
}