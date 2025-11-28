import React from 'react'
import { Outlet, useLocation } from 'react-router-dom'
import Header from '../header/Header'
import Footer from '../footer/Footer'
import SideBar from '../sideBar/SideBar'
import PageTitle from './PageTitle'
import { SidebarProvider } from 'context/SidebarContext'
import { LoadingProvider } from 'context/LoadingContext'
import { DashboardProvider } from 'context/DashBoardContext'
import { useReRender } from 'context/ReRenderContext'

function AdminLayout() {
    const location = useLocation();
    const pathname = location.pathname !== '/' ? location.pathname.replace('admin/', '').replace(/-/g, ' ') : 'Dashboard';
    const title = pathname.charAt(0).toUpperCase() + pathname.slice(1);
    const { renderKey } = useReRender();
    
    return (
        <>
            <SidebarProvider >
                {(location.pathname !== '/signin' || location.pathname !== '/signup') && (
                    <>
                        <Header />
                        <SideBar />
                    </>
                )}
                
                <main id={`main`} className={`main`} >
                    <PageTitle title={title} location={location} />
                    <section className={`section`} >
                        <div className={`w-100`} >
                            <div className={`col-lg-12`} >
                                <div className={`px-1 mb-1`} >
                                    <LoadingProvider>
                                        <DashboardProvider>
                                            <Outlet key={renderKey + 1} />
                                        </DashboardProvider>
                                    </LoadingProvider>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>
                {(location.pathname !== '/signin' || location.pathname !== '/signup') && <Footer />}
            </SidebarProvider >
        </>
    )
}

export default AdminLayout