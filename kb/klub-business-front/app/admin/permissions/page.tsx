'use client';

import { TableView } from '@/components/table/TableView';
import { useState } from 'react';
import UseFilter from './filter';

export default function PermissionsPage() {
  const [filters, setFilters] = useState<Record<string, any>>({});
  const [showFilters, setShowFilters] = useState(false);
  
  return (
    <>
      <UseFilter setFilters={setFilters} visible={showFilters} />
      <TableView
        resource={`permission`}
        addUrl={`/admin/permissions/add`}
        editUrl={(id) => `/admin/permissions/${id}/edit`}
        viewUrl={(id) => `/admin/permissions/${id}`}
        canDelete={true}
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'name', label: 'Name' },
          { key: 'description', label: 'Description' },
          { key: 'resource', label: 'Resource' },
          { key: 'action', label: 'Action' },
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
