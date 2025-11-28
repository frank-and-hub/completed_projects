// next.config.mjs
import withBundleAnalyzer from "@next/bundle-analyzer";

const bundleAnalyzer = withBundleAnalyzer({
  enabled: process.env.ANALYZE === "true",
});

/** @type {import('next').NextConfig} */
const nextConfig = {
  trailingSlash: false,
  experimental: {
    optimizePackageImports: ["@mantine/core", "@mantine/hooks"],
  },
  images: {
    unoptimized: true,
    domains: ["pijar-df234.web.app"],
  },
  output: "export",
  swcMinify: true,
};

export default bundleAnalyzer(nextConfig);
