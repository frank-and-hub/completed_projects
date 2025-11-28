import type { NextConfig } from "next";

const allowedDevOrigins = [
  'http://10.59.145.26:3000',
  'http://localhost:3000'
];

const nextConfig: NextConfig = {
  /* config options here */
  experimental: {
    allowedDevOrigins,
  },
};

export default nextConfig;

// import bundleAnalyzer from '@next/bundle-analyzer';

// const withBundleAnalyzer = bundleAnalyzer({
//   enabled: process.env.ANALYZE === 'true',
// });

// export default withBundleAnalyzer({
//   reactStrictMode: false,
//   eslint: {
//     ignoreDuringBuilds: true,
//   },
//   experimental: {
//     optimizePackageImports: ['@mantine/core', '@mantine/hooks'],
//   },
// });