import { QueryClient } from "@tanstack/react-query";

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      // Configure default options (optional)
      //   staleTime: 5 * 60 * 1000, // 5 minutes
      // Refetch data when the component mounts or the window is focused
      refetchOnMount: true,
      refetchOnWindowFocus: true,
      // staleTime: 5 * 60 * 1000, // 5 minutes
      gcTime: 10 * 60 * 1000, // 10 minutes
    },
  },
});
