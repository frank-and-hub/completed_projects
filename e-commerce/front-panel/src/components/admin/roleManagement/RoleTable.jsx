import React from 'react'
import Table from 'components/admin/table/Table'


function RoleTable() {
    const module = 'roles';

    const handlers = {
        url: `/${module}`,
        handelView: true,
        handelEdit: true,
        handelDelete: true,
        handelFilter: null,
        handelCreate: true,
        moduleName: module,
        filter: {}
    };

    return (
        <>
            <Table table  {...handlers} />
        </>
    )
}

export default RoleTable