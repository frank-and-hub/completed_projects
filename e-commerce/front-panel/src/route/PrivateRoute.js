import React from 'react'
import { Navigate } from 'react-router-dom'
import { useSelector } from 'react-redux'
import { useLocation } from 'react-router-dom'

const PrivateRoute = ({ children }) => {
  const location = useLocation();
  const path = location.pathname.indexOf('admin');
  const role = localStorage.getItem('role');
  const token = useSelector((state) => (state.auth.token)) ?? localStorage.getItem('token');
  if (!token && path && (role !== 'customer' || role !== 'guest-user')) return <Navigate to={`/admin/signin`} />;
  return children;
}

export default PrivateRoute;
