'use client';

import { useMediaQuery } from '@mantine/hooks';
import TableButton from '@/components/table/TableButton';
import TableDataShort from '@/components/table/TableDataShort';
import { ArrowLeftIcon, ArrowRightIcon } from '@heroicons/react/24/outline';

interface TablePaginationProps {
  total: number;
  totalPages: number;
  page: number;
  limit: number;
  setPage: (page: number) => void;
  setLimit: (limit: number) => void;
}

export default function SortTablePagination({
  total,
  totalPages,
  page,
  limit,
  setPage,
  setLimit,
}: TablePaginationProps) {
  if (totalPages <= 1) return null;

  const isSmallScreen = useMediaQuery('(max-width: 639px)');

  // Calculate buttons to show (max 4, including Prev and Next)
  const pagesToShow = [];

  // Always include Prev and Next buttons
  const maxNumericButtons = 2; // Since we want max 4 buttons total (Prev, Next, and 2 numeric)
  const startPage = Math.max(1, page - 1);
  const endPage = Math.min(totalPages, page + 1);

  // Add ellipsis or page numbers
  if (totalPages <= 4) {
    // If total pages are 4 or less, show all pages
    for (let pageNumber = 1; pageNumber <= totalPages; pageNumber++) {
      pagesToShow.push(
        <TableButton
          key={`${pageNumber}_0`}
          name={pageNumber.toString()}
          onClick={() => setPage(pageNumber)}
          className={pageNumber === page ? 'border-gray-500 text-white' : ''}
        />
      );
    }
  } else {
    // Show current page and one adjacent page (or ellipsis)
    if (page > 2) {
      pagesToShow.push(
        <span
          key="ellipsis-start"
          className="p-1 text-gray-800 text-xl select-none"
        >
          ...
        </span>
      );
    }

    // Show current page and one previous or next page
    for (
      let pageNumber = startPage;
      pageNumber <= Math.min(endPage, startPage + maxNumericButtons - 1);
      pageNumber++
    ) {
      pagesToShow.push(
        <TableButton
          key={`${pageNumber}_0`}
          name={pageNumber.toString()}
          onClick={() => setPage(pageNumber)}
          className={pageNumber === page ? 'border-gray-500 text-white' : ''}
        />
      );
    }

    if (page < totalPages - 1) {
      pagesToShow.push(
        <span
          key="ellipsis-end"
          className="p-1 text-gray-800 text-xl select-none"
        >
          ...
        </span>
      );
    }
  }

  return (
    <div
      className="mt-4 flex flex-col md:flex-row sm:flex-2 md:items-center md:justify-between gap-4"
    >
      <div className="hidden md:block">
        <TableDataShort limit={limit} setLimit={setLimit} showLabel={true} />
      </div>

      <div className="flex flex-col items-center gap-2 w-full md:w-auto">
        <div className="text-xs text-gray-500 dark:text-gray-100">
          Page {page} of {totalPages} | Total: {total}
        </div>

        <div className="flex flex-wrap min-w-80 p-0 items-center justify-center gap-1">
          <TableButton
            name={isSmallScreen ? <ArrowLeftIcon width={15} height={15} /> : 'Prev'}
            disabled={page === 1}
            onClick={() => setPage(Math.max(page - 1, 1))}
          />
          {pagesToShow}
          <TableButton
            name={isSmallScreen ? <ArrowRightIcon width={15} height={15} /> : 'Next'}
            disabled={page === totalPages}
            onClick={() => setPage(Math.min(page + 1, totalPages))}
          />
        </div>
      </div>
    </div>
  );
}