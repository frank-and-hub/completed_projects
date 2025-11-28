"use client";

import { get, post, patch } from "@/utils/axios";
import { CommonQueryParams } from "@/types/CommonQueryParams";
import { NewRoleData, RoleDetails } from "@/types/Role";
import { sanitizePatchData } from "@/utils/helpers";

export const getRoleList = async (params: CommonQueryParams) => {
  const searchParams = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      searchParams.set(key, String(value));
    }
  });

  const url = `v1/roles?${searchParams.toString()}`;
  return await get(url);
}

export const createNewRole = async (roleData: Partial<NewRoleData>) => {
  const url = `v1/roles`;
  return await post(url, roleData);
}

export const getRoleDetails = async (id: string) => {
  if (!id) throw Error("ID is required to update role");
  const url = `v1/roles/${id}`;
  return await get(url);
}

export const updateRoleDetails = async (data: any, id: string) => {
  if (!id) throw Error("ID is required to update role");
   const allowedFields = [
    'name',
    'description',
    'status',
  ];
  const sanitizedData = sanitizePatchData(data, allowedFields);
  const url = `v1/roles/${id}`;
  return await patch(url, sanitizedData);
}