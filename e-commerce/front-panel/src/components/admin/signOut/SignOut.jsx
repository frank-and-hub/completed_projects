import React from 'react'
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from 'utils/AuthContext';
import { notifySuccess } from 'components/admin/comman/notification/Notification';
import { post } from 'utils/AxiosUtils';

const SignOut = () => {
  const navigate = useNavigate();
  const { logout } = useAuth();

  const handleSignOut = async () => {
    const res = await post(`/sign-out`);
    if (res) {
      await logout();
      notifySuccess(`User sign out...`);
    }
    navigate('/admin/signin', { replace: true })
  }

  return (
    <li>
      <Link
        className={`dropdown-item d-flex align-items-center`}
        onClick={handleSignOut}
      >
        <i className={`bi bi-box-arrow-right`} ></i>
        <span>Sign Out</span>
      </Link>
    </li>
  );
}

export default SignOut;
