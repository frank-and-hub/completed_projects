'use client';

import { useMediaQuery } from "@mantine/hooks";
import TableButton from "@/components/table/TableButton";
import TableDataShort from "@/components/table/TableDataShort";
import { ArrowLeftIcon, ArrowRightIcon } from "@heroicons/react/24/outline";

interface TablePaginationProps {
  total: number;
  totalPages: number;
  page: number;
  limit: number;
  setPage: (page: number) => void;
  setLimit: (limit: number) => void;
}

export default function TablePagination({
  total,
  totalPages,
  page,
  limit,
  setPage,
  setLimit,
}: TablePaginationProps) {
  if (totalPages <= 1) return null;

  const pagesToShow = [];

  for (let pageNumber = 1; pageNumber <= totalPages; pageNumber++) {
    const shouldShow =
      pageNumber === 1 ||
      pageNumber === totalPages ||
      Math.abs(page - pageNumber) <= 1;

    const showEllipsisBefore = pageNumber === page - 2 && page > 4;
    const showEllipsisAfter = pageNumber === page + 2 && page < totalPages - 3;

    if (shouldShow) {
      pagesToShow.push(
        <TableButton
          key={`${pageNumber}_0`}
          name={pageNumber.toString()}
          onClick={() => setPage(pageNumber)}
        />
      );
    } else if (showEllipsisBefore || showEllipsisAfter) {
      pagesToShow.push(
        <span
          key={`ellipsis-${pageNumber}`}
          className={`px-3 py-1 text-gray-800 text-xl select-none`}
        >
          ...
        </span>
      );
    }
  }

  const isSmallScreen = useMediaQuery('(max-width: 719px)');

  return (
    <div className={`mt-4 flex flex-col md:flex-row sm:flex-2 md:items-center md:justify-between gap-4`}>
      <div className={`hidden md:block`}>
        <TableDataShort limit={limit} setLimit={setLimit} showLabel={true} />
      </div>

      <div className={`flex flex-col items-center gap-2 w-full md:w-auto`}>
        <div className={`text-xs text-gray-500 dark:text-gray-100`}>
          Page {page} of {totalPages} | Total: {total}
        </div>

        <div className={`flex flex-wrap min-w-80 p-0 items-center justify-center gap-1`}>
          <TableButton
            name={isSmallScreen ? <ArrowLeftIcon width={15} height={15}/> : `Prev`}
            disabled={page === 1}
            onClick={() => setPage(Math.max(page - 1, 1))}
          />
          {pagesToShow}
          <TableButton
            name={isSmallScreen ? <ArrowRightIcon width={15} height={15}/> : `Next`}
            disabled={page === totalPages}
            onClick={() => setPage(Math.min(page + 1, totalPages))}
          />
        </div>
      </div>
    </div>
  );
}
