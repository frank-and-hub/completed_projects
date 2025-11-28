import React from 'react'
import Logo from './logo/Logo'
import Messages from './messages/Messages'
import SearchBar from './searchBar/SearchBar'
import UserProfile from './userDetail/User'
// import Notifications from './notifications/Notifications'
// import notificationsData from './notifications/notificationsData'
// import { useSelector } from 'react-redux'

function Header() {
    // const notificationsData = useSelector((state) => (state.alert.notifications));
    return (
        <>
            <header id="header" className={`header fixed-top d-flex align-items-center mx-2 mt-1 mb-3 rounded-50 shadow`}>
                <Logo />
                <SearchBar />
                <nav className={`header-nav ms-auto`}>
                    <ul className={`d-flex align-items-center`}>
                        {/* <Notifications notifications={notificationsData} /> */}
                        <Messages />
                        <UserProfile />
                    </ul>
                </nav>
            </header>
        </>
    )
}

export default Header