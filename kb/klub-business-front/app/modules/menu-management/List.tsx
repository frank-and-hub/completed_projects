'use client';

import React, { useState } from 'react';
import { TableView } from '@/components/table/TableView';

function List() {

  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);

  return (
    <>
      <TableView
        resource={`menu`}
        addUrl={`/admin/menus/add-new`}
        editUrl={(id) => `/admin/menus/edit/${id}`}
        viewUrl={(id) => `/admin/menus/view/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'name', label: 'Name' },
          { key: 'status', label: 'Status' },
        ]}
        filters={[
          filters,
          setFilters,
          showFilters,
          setShowFilters,
        ]}
      />
    </>
  );
}

export default List;
