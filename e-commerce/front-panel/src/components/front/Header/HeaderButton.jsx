import React from 'react'

function HeaderButton() {
        return (
            <>
                <div className={`d-lg-none flex-sm-fill mt-3 mb-4 col-7 col-sm-auto pr-3`}>
                    <div className={`input-group`}>
                        <input type={`text`} className={`form-control`} id={`inputMobileSearch`} placeholder={`Search ...`} />
                        <div className={`input-group-text`}>
                            <i className={`fa fa-fw fa-search`}></i>
                        </div>
                    </div>
                </div>
            </>
        );
    }

export default HeaderButton