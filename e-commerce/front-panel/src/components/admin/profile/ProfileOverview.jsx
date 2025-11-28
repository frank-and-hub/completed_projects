import React from 'react'

function ProfileOverview({ user }) {

    const UserData = {
        'First Name': user?.name?.first_name,
        'Middle Name': user?.name?.middle_name,
        'Last Name': user?.name?.last_name,
        'Phone': user?.phone,
        'Email': user?.email,
        'Gender': user?.gender,
        'Address': user?.address,
        'City': user?.city,
        'State': user?.state,
        'Zip Code': user?.zipcode,
        'Time Zone': user?.tmezone
    };
    return (
        <>
            <h5 className={`card-title`}>About</h5>
            <p className={`small fst-italic`}>{user?.about}</p>

            <h5 className={`card-title`}>Profile Details</h5>

            {Object.entries(UserData).map(([key, value], index) => (
                <div className={`row`} key={index}>
                    <div className={`col-lg-3 col-md-4 label text-start`}>{key}</div>
                    <div className={`col-lg-9 col-md-8 text-start`}>{value}</div>
                </div>
            ))}
        </>
    )
}

export default ProfileOverview