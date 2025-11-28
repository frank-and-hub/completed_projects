import { LoadOptions } from 'react-select-async-paginate';

function useGetQuerySearch(
  labelKey: string,
  queryFn?: (params: any) => Promise<any>,
  apiEnabled?: boolean
) {
  // const [apiData, setApiData] = useState<SelectOptionType[]>([]);

  const loadOptions: LoadOptions<
    SelectOptionType,
    any,
    { page: number }
  > = async (searchValue, loadedOptions, additionalData) => {
    if (!apiEnabled) {
      return {
        options: [...loadedOptions], // Combine loaded options with new options
      };
    }

    const currentPage = additionalData?.page || 1; // Default to page 1 if not provided

    // Use the query function to fetch data based on searchValue and currentPage
    const response = await queryFn?.({
      page: currentPage,
      search: searchValue,
    });

    // Extract new options from the response
    const newOptions: SelectOptionType[] =
      response?.data?.map((item: any) => ({
        id: item?.id ?? `${loadedOptions.length}`, // Generate unique id
        value: item?.id,
        label: item?.[labelKey] ?? 'N/A',
        item, // Keep the original item for further reference
      })) || [];

    // Determine if there are more pages based on the API response
    const hasMore = response?.meta?.total_page > response?.meta?.current_page;

    // Return the options and pagination information
    // let data = [...loadedOptions, ...newOptions];

    // setApiData(data);
    return {
      options: newOptions, // Combine loaded options with new options
      hasMore, // Indicates whether more options are available
      additional: { page: currentPage + 1 }, // Increment page number for next load
    };
  };

  return {
    loadOptions, // Return the loadOptions function
    // apiData,
    // isLoading:
    //   queryData?.isLoading ||
    //   queryData?.isFetchingNextPage ||
    //   queryData?.isFetching,
    // options,
  };
}

export default useGetQuerySearch;
