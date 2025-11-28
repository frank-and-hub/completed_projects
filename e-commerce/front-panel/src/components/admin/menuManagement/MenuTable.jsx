import React from 'react'
import Table from 'components/admin/table/Table'

function MenuTable() {
    const module = 'menus';

    const handlers = {
        url: `/${module}`,
        handelView: true,
        handelEdit: true,
        handelDelete: true,
        handelFilter: null,
        handelCreate: true,
        moduleName: module,
        filter: { filter_type: true }
    };

    return (
        <>
            <Table table {...handlers} />
        </>
    )
}

export default MenuTable