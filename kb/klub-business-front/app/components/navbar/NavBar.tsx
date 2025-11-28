import Link from "next/link";
import LogoutButton from "./SignOut";

export default function NavBar() {
    return (
        <>
            <div className="nav-header">
                <Link href="#" className="brand-logo">
                    {/* <img className="logo-abbr" src="../images/logo.png" alt="" /> */}
                    {/* <img className="logo-compact" src="../images/logo-text.png" alt="" /> */}
                    {/* <img className="brand-title" src="../images/logo-text.png" alt="" /> */}
                </Link>

                {/* <div className="nav-control">
                    <div className="hamburger">
                        <span className="line"></span>
                        <span className="line"></span>
                        <span className="line"></span>
                    </div>
                </div> */}
            </div>
        </>
    );
}