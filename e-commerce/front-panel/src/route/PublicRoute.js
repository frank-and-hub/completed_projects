import React from 'react'
import { useSelector } from 'react-redux'
import { Navigate } from 'react-router-dom'
import { useLocation } from 'react-router-dom'

const PublicRoute = ({ children }) => {
  const location = useLocation();
  const path = location.pathname.indexOf('admin');
  const token = useSelector((state) => (state.auth.token)) ?? localStorage.getItem('token');
  if (!token && !path) return <Navigate to={`/`} />;
  return children;
}

export default PublicRoute;
