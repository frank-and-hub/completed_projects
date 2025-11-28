import React, { useState } from 'react'
import { useSelector } from 'react-redux'
import ProfileCard from './ProfileCard'
import ProfileChangePassword from './ProfileChangePassword'
import ProfileForm from './ProfileForm'
import ProfileOverview from './ProfileOverview'
import AboutProfile from './AboutProfile'

function Profile() {
  const user = useSelector((state) => (state.auth.user));

  // State to track the active tab
  const [activeTab, setActiveTab] = useState("profile-overview");

  const tabs = [
    { id: "profile-overview", class: "profile-overview", label: "Overview", component: <ProfileOverview user={user} /> },
    { id: "profile-edit", class: "profile-edit", label: "Edit Profile", component: <ProfileForm user={user} /> },
    { id: "profile-settings", class: "", label: "About", component: <AboutProfile /> },
    { id: "profile-change-password", class: "", label: "Change Password", component: <ProfileChangePassword user={user} /> }
  ];

  return (
    <section className={`section profile m-0`}>
      <div className={`row`}>         
        <div className={`col-xl-8`}>
          <div className={`card`}>
            <div className={`card-body pt-3`}>
              <ul className={`nav nav-tabs nav-tabs-bordered justify-content-around`}>
                {tabs.map((tab, index) => (
                  <li className={`nav-item`} key={index}>
                    <button
                      className={`nav-link ${activeTab === tab.id ? 'active' : ''}`}
                      data-bs-toggle={`tab`}
                      onClick={() => setActiveTab(tab.id)} // Set active tab on click
                    >
                      {tab.label}
                    </button>
                  </li>
                ))}
              </ul>
              <div className={`tab-content pt-2`}>
                {tabs.map((tab, index) => (
                  <div
                    key={index}
                    className={`tab-pane fade ${tab.class} ${activeTab === tab.id ? "active show" : ""}`}
                    id={tab.id}
                  >
                    {tab.component}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
        <div className={`col-xl-4`}>
          <ProfileCard user={user} />
        </div> 
      </div>
    </section>
  );
}

export default Profile;
