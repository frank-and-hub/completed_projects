"use client";

import { AppShell, Box, rem, ScrollArea, Space } from '@mantine/core';
import { useDisclosure } from '@mantine/hooks';
import Header from '@/components/header/Header';
import SideBar from '@/components/sideBar/SideBar';
import Footer from '@/components/footer/Footer';
import Spinner from '@/components/spinner/Spinner';
import PageBreadcrumbs from '@/components/breadcrumbs/Breadcrumb';
import CommanPageTitle from '@/components/titles/CommanPageTitle';
import AuthProvider, { useAuth } from '@/context/AuthContext';
import { mainStyle } from '@/utils/style';

type AdminLayoutProps = {
  children: React.ReactNode;
};

export default function AdminLayout({ children }: AdminLayoutProps) {

  const [opened, { toggle }] = useDisclosure();
  // const pinned = useHeadroom({ fixedAt: 10 });
  const { loading } = useAuth();

  return (
    <AuthProvider>
      <AppShell padding={{ base: `xs`, sm: `sm`, lg: `md` }} header={{ height: { base: rem(75) }, collapsed: false }} navbar={{ width: { sm: 180, lg: 310, xl: 330 }, breakpoint: `sm`, collapsed: { mobile: !opened }, }} withBorder={false} transitionDuration={800} transitionTimingFunction={`ease`} translate={`yes`} >
        {loading && <Spinner />}
        <AppShell.Header withBorder={false} px={`xs`} className={`bg-transparent fixed top-0 left-0 right-0`}>
          <Header opened={opened} toggle={toggle} />
        </AppShell.Header>

        <AppShell.Navbar withBorder={false} px={`xs`} className={`overflow-y-auto top-0 z-0 h-screen`}>
          <AppShell.Section grow component={ScrollArea} type={`scroll`} scrollbarSize={rem(0.01)} offsetScrollbars pt={0} px={{ xs: 0, base: rem(7), xl: 0, lg: 0, md: 0, sm: 0 }} className={`transition-all duration-100 ease-in-out h-fit bg-transparent`} translate={`yes`} >
            <Box className={`mt-23`} px={0} translate={`yes`} >
              <SideBar action={toggle} opened={opened} />
            </Box>
          </AppShell.Section>
        </AppShell.Navbar>

        <AppShell.Main className={`overflow-y-auto px-0`}>
          <Box className={mainStyle} >
            <span className={`flex flex-col md:flex-row justify-between md:items-center gap-4 md:gap-0 w-full sm:mb-5`}>
              <PageBreadcrumbs />
              <CommanPageTitle />
            </span>
            <div className={`max-h-fit`}>{children}</div>
          </Box>
        </AppShell.Main>

        <AppShell.Footer>
          <Footer />
        </AppShell.Footer>

      </AppShell>
    </AuthProvider>
  );
}