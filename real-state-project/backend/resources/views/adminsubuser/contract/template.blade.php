<style>
    .input_field {
        background: #eef2f2 !important;
        border: 1px solid #e3eded !important;
        padding: 0px !important;
        color: black;
        flex-direction: row;
        /* width: 75%; */
        display: -webkit-box;
        text-align: center;
        min-height: 1.2rem;
        -webkit-box-orient: vertical;
    }

    .p50 {
        padding: 5vw;
    }

    p>span {
        display: inline-block !important;
    }

    .w10 {
        width: 10% !important;
    }

    .w20 {
        width: 15% !important;
    }

    .w30 {
        width: 20% !important;
    }

    .w40 {
        width: 30% !important;
    }

    .w50 {
        width: 35% !important;
    }

    .w60 {
        width: 40% !important;
    }

    .w70 {
        width: 45% !important;
    }

    .w80 {
        width: 50% !important;
    }

    .w-80 {
        width: 50% !important;
    }

    .w-90 {
        width: 60% !important;
    }

    small {
        font-size: 10px !important;
    }

    p,
    li {
        align-items: center;
        position: relative !important;
        font-size: 12px;
        flex-direction: row;
        flex-wrap: nowrap;
    }

    /* p {
        white-space: nowrap !important;
    } */

    ul li,
    ol li,
    dl li {
        line-height: 1.8;
    }

    .justify-content-center {
        justify-content: center !important;
    }

    .reverse {
        justify-content: end !important;
        margin: 0px !important;
        padding: 0px !important;
        align-items: center !important;
    }

    .p0 {
        padding: 0px !important;
    }

    .m0 {
        margin: 0px !important;
    }

    .checkbox {
        width: 2% !important;
        border: none !important;
        background-color: transparent !important;
    }

    .custom_css {
        display: flex !important;
        justify-content: space-between;
        align-items: center !important;
        width: 100%;
    }

    table tbody tr td {
        border: none;
        padding: 7px 12px 7px 12px !important;
        font-size: 12px !important;
    }

    table thead tr th {
        background-color: #eef2f2 !important;
        padding: 10px 12px 10px 12px !important;
    }

    .note-editor .note-editing-area .note-editable table td,
    .note-editor .note-editing-area .note-editable table th {
        border: none !important;
    }

    small {
        margin-left: 5px;
    }

</style>
<div class="container p50">
    <h1 style="text-align: center;">
        <img style="width: 425px;" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDI1IiBoZWlnaHQ9Ijc4IiB2aWV3Qm94PSIwIDAgNDI1IDc4IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfMTE2MF8zMDc0KSI+CjxwYXRoIGQ9Ik0xNDIuNjE1IDI3LjIyODhIMTQwLjQ2NFYzOC4xMjQxSDE0Mi42MTVDMTQ0LjM1IDM4LjEyNDEgMTQ1LjY4MyAzNy42MzQgMTQ2LjYxNCAzNi42NTM4QzE0Ny41NjkgMzUuNjQ4NCAxNDguMDQ3IDM0LjMxNjQgMTQ4LjA0NyAzMi42NTc2QzE0OC4wNDcgMzAuOTczNiAxNDcuNTY5IDI5LjY1NDEgMTQ2LjYxNCAyOC42OTkxQzE0NS42ODMgMjcuNzE4OSAxNDQuMzUgMjcuMjI4OCAxNDIuNjE1IDI3LjIyODhaTTEzNi42OTIgMjMuNzIyN0gxNDMuNzQ2QzE0My44MjIgMjMuNzIyNyAxNDMuODk3IDIzLjcyMjcgMTQzLjk3MyAyMy43MjI3QzE0OC4xMjMgMjMuNzIyNyAxNTEuMjA0IDI1LjAwNDUgMTUzLjIxNiAyNy41NjgxQzE1NC4zMjIgMjguOTc1NSAxNTQuODc2IDMwLjY3MiAxNTQuODc2IDMyLjY1NzZDMTU0Ljg3NiAzNC42NDMxIDE1NC4zMjIgMzYuMzI3IDE1My4yMTYgMzcuNzA5NEMxNTEuMTc4IDQwLjI5ODEgMTQ4LjAyMiA0MS41OTI1IDE0My43NDYgNDEuNTkyNUgxNDAuNDI2VjQ3LjU4NjhDMTQwLjQyNiA0OC45NDQgMTQwLjg0MSA0OS44MzYyIDE0MS42NzEgNTAuMjYzNUMxNDAuNzE2IDUxLjM0NDIgMTM5LjU1OSA1MS44ODQ2IDEzOC4yMDEgNTEuODg0NkMxMzUuNzM2IDUxLjg4NDYgMTM0LjUwMyA1MC4zODkxIDEzNC41MDMgNDcuMzk4M1YyNy45ODI4QzEzNC41MDMgMjYuNzAxIDEzNC4wNzYgMjUuODMzOSAxMzMuMjIxIDI1LjM4MTVDMTM0LjE1MSAyNC4yNzU2IDEzNS4zMDggMjMuNzIyNyAxMzYuNjkyIDIzLjcyMjdaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0xNjYuMzQ0IDM0LjIwNUMxNjQuMTgxIDM0LjIwNSAxNjMuMDk5IDM2LjA5IDE2My4wOTkgMzkuODZDMTYzLjA5OSA0MC40MTMgMTYzLjEyNSA0MC45OTEgMTYzLjE3NSA0MS41OTQyQzE2My40NzcgNDYuNTQ1NSAxNjQuODYgNDkuMDIxMSAxNjcuMzI1IDQ5LjAyMTFDMTY5LjQ4OCA0OS4wMjExIDE3MC41NjkgNDcuMTIzNiAxNzAuNTY5IDQzLjMyODRDMTcwLjU2OSA0Mi44MDA2IDE3MC41NDQgNDIuMjIyNiAxNzAuNDk0IDQxLjU5NDJDMTcwLjE0MiAzNi42NjgxIDE2OC43NTggMzQuMjA1IDE2Ni4zNDQgMzQuMjA1Wk0xNTkuODE3IDM0LjAxNjVDMTYxLjYwMyAzMi4yMzIxIDE2My45NDIgMzEuMzM5OCAxNjYuODM0IDMxLjMzOThDMTY5LjcyNyAzMS4zMzk4IDE3Mi4wNjYgMzIuMjMyMSAxNzMuODUxIDM0LjAxNjVDMTc1LjYzNyAzNS43NzU5IDE3Ni41MyAzOC4zMDE4IDE3Ni41MyA0MS41OTQyQzE3Ni41MyA0NC44ODY3IDE3NS42MjUgNDcuNDI1MiAxNzMuODE0IDQ5LjIwOTZDMTcyLjAyOCA1MC45OTQxIDE2OS42ODkgNTEuODg2MyAxNjYuNzk3IDUxLjg4NjNDMTYzLjkyOSA1MS44ODYzIDE2MS42MDMgNTAuOTk0MSAxNTkuODE3IDQ5LjIwOTZDMTU4LjAzMiA0Ny40MjUyIDE1Ny4xMzkgNDQuODg2NyAxNTcuMTM5IDQxLjU5NDJDMTU3LjEzOSAzOC4zMDE4IDE1OC4wMzIgMzUuNzc1OSAxNTkuODE3IDM0LjAxNjVaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0xOTYuNzE0IDM1LjgyNjFDMTk2LjcxNCAzNi43NTYxIDE5Ni40MjUgMzcuNDg0OSAxOTUuODQ3IDM4LjAxMjdDMTk1LjI2OCAzOC41MTU0IDE5NC41MDEgMzguNzY2NyAxOTMuNTQ1IDM4Ljc2NjdDMTkyLjU4OSAzOC43NjY3IDE5MS43MzQgMzguNDc3NyAxOTAuOTggMzcuODk5NkMxOTEuMDU1IDM3LjU5OCAxOTEuMDkzIDM3LjE5NTkgMTkxLjA5MyAzNi42OTMyQzE5MS4wOTMgMzYuMTkwNiAxOTAuOTQyIDM1LjczODIgMTkwLjY0IDM1LjMzNkMxOTAuMzY0IDM0LjkzMzkgMTg5Ljk3NCAzNC43MzI4IDE4OS40NzEgMzQuNzMyOEMxODguNDkgMzQuNzMyOCAxODcuNjM1IDM1LjM5ODkgMTg2LjkwNSAzNi43MzA5QzE4Ni4yMDEgMzguMDYzIDE4NS44NDkgMzkuNzIxOCAxODUuODQ5IDQxLjcwNzNDMTg1Ljg0OSA0My42Njc3IDE4Ni4yNzcgNDUuMTc1NyAxODcuMTMyIDQ2LjIzMTNDMTg4LjAxMiA0Ny4yNjE4IDE4OS4xNTYgNDcuNzc3IDE5MC41NjUgNDcuNzc3QzE5Mi42NTIgNDcuNzc3IDE5NC40IDQ2Ljk4NTMgMTk1LjgwOSA0NS40MDE5QzE5Ni4zODcgNDUuNjI4MSAxOTYuNjc2IDQ2LjE1NTkgMTk2LjY3NiA0Ni45ODUzQzE5Ni42NzYgNDguMjY3MSAxOTUuOTYgNDkuNDEwNyAxOTQuNTI2IDUwLjQxNkMxOTMuMTE4IDUxLjM5NjIgMTkxLjQzMyA1MS44ODYzIDE4OS40NzEgNTEuODg2M0MxODYuNTI4IDUxLjg4NjMgMTg0LjIzOSA1MS4wNDQ0IDE4Mi42MDUgNDkuMzYwNEMxODAuOTcgNDcuNjc2NSAxODAuMTUyIDQ1LjM1MTcgMTgwLjE1MiA0Mi4zODU5QzE4MC4xNTIgMzkuNDIwMiAxODEuMTA4IDM2Ljg0NCAxODMuMDIgMzQuNjU3NEMxODQuOTMxIDMyLjQ0NTcgMTg3LjM4MyAzMS4zMzk4IDE5MC4zNzYgMzEuMzM5OEMxOTIuMDYxIDMxLjMzOTggMTkzLjUzMyAzMS43NTQ1IDE5NC43OSAzMi41ODM5QzE5Ni4wNzMgMzMuMzg4MiAxOTYuNzE0IDM0LjQ2ODkgMTk2LjcxNCAzNS44MjYxWiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMjA2LjkzOCAyNi40MDA0VjM5LjEwNTNDMjExLjYxNiAzNi45MTg3IDIxMy45NTUgMzQuMzI5OSAyMTMuOTU1IDMxLjMzOTFDMjE1LjY0IDMxLjMzOTEgMjE2Ljk5OSAzMS42Nzg0IDIxOC4wMyAzMi4zNTdDMjE5LjA2MSAzMy4wMzU2IDIxOS41NzYgMzMuOTkwNiAyMTkuNTc2IDM1LjIyMjJDMjE5LjU3NiAzNi40Mjg2IDIxOC44ODUgMzcuNDk2NyAyMTcuNTAyIDM4LjQyNjdDMjE2LjE0MyAzOS4zNTY2IDIxMy45OTMgNDAuMjYxNCAyMTEuMDUgNDEuMTQxMUMyMTMuNDkgNDMuNjA0MSAyMTUuMzAxIDQ1LjE3NSAyMTYuNDgzIDQ1Ljg1MzZDMjE3LjY2NSA0Ni41MzIyIDIxOC45ODUgNDYuODcxNSAyMjAuNDQ0IDQ2Ljg3MTVDMjIwLjQ0NCA0OC40NTQ5IDIyMC4wNjcgNDkuNjg2NCAyMTkuMzEyIDUwLjU2NjFDMjE4LjU1OCA1MS40NDU3IDIxNy41MTQgNTEuODg1NiAyMTYuMTgxIDUxLjg4NTZDMjE0Ljg0OCA1MS44ODU2IDIxMy40NzcgNTEuMjE5NSAyMTIuMDY5IDQ5Ljg4NzVDMjEwLjY4NiA0OC41MzAzIDIwOC45NzUgNDYuMjA1NCAyMDYuOTM4IDQyLjkxM1Y0Ny41ODc4QzIwNi45MzggNDguOTQ1IDIwNy4zNTMgNDkuODM3MiAyMDguMTgzIDUwLjI2NDVDMjA3LjIyNyA1MS4zNDUyIDIwNi4wNyA1MS44ODU2IDIwNC43MTIgNTEuODg1NkMyMDIuMjQ4IDUxLjg4NTYgMjAxLjAxNSA1MC4zOTAxIDIwMS4wMTUgNDcuMzk5M1YyNi4xNzQyQzIwMS4wMTUgMjQuOTE3NSAyMDAuNTg4IDI0LjAyNTMgMTk5LjczMiAyMy40OTc1QzIwMC42ODggMjIuNDQxOSAyMDEuODQ1IDIxLjkxNDEgMjAzLjIwMyAyMS45MTQxQzIwNS42OTMgMjEuOTE0MSAyMDYuOTM4IDIzLjQwOTUgMjA2LjkzOCAyNi40MDA0WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMjMzLjg3NCAzOC45OTI5QzIzNC4zMjcgMzguOTQyNyAyMzQuNjY2IDM4LjgwNDQgMjM0Ljg5MiAzOC41NzgyQzIzNS4xMTkgMzguMzI2OSAyMzUuMjMyIDM3Ljg2MTkgMjM1LjIzMiAzNy4xODMzQzIzNS4yMzIgMzYuNTA0NyAyMzUuMDMxIDM1Ljg4OSAyMzQuNjI4IDM1LjMzNkMyMzQuMjUxIDM0Ljc1OCAyMzMuNzIzIDM0LjQ2ODkgMjMzLjA0NCAzNC40Njg5QzIzMi4zNjUgMzQuNDY4OSAyMzEuNzIzIDM0Ljg5NjIgMjMxLjEyIDM1Ljc1MDdDMjMwLjUxNiAzNi42MDUzIDIzMC4xMTQgMzcuODQ5NCAyMjkuOTEzIDM5LjQ4M0wyMzMuODc0IDM4Ljk5MjlaTTIzNC41MTUgNDcuNzc3QzIzNi42MDMgNDcuNzc3IDIzOC4zNTEgNDYuOTg1MyAyMzkuNzU5IDQ1LjQwMTlDMjQwLjMzOCA0NS42NTMzIDI0MC42MjcgNDYuMTgxMSAyNDAuNjI3IDQ2Ljk4NTNDMjQwLjYyNyA0OC4xOTE3IDIzOS45MSA0OS4zMTAyIDIzOC40NzYgNTAuMzQwNkMyMzcuMDQzIDUxLjM3MTEgMjM1LjM1OCA1MS44ODYzIDIzMy40MjEgNTEuODg2M0MyMzAuNDI4IDUxLjg4NjMgMjI4LjA4OSA1MS4wNjk1IDIyNi40MDQgNDkuNDM1OEMyMjQuNzQ0IDQ3LjgwMjIgMjIzLjkxNCA0NS40NjQ4IDIyMy45MTQgNDIuNDIzNkMyMjMuOTE0IDM5LjM1NzQgMjI0LjgzMiAzNi43NDM1IDIyNi42NjggMzQuNTgyQzIyOC41MDQgMzIuNDIwNiAyMzAuODMxIDMxLjMzOTggMjMzLjY0NyAzMS4zMzk4QzIzNS44MSAzMS4zMzk4IDIzNy41OTYgMzIuMDQzNiAyMzkuMDA1IDMzLjQ1MUMyNDAuNDEzIDM0LjgzMzQgMjQxLjExNyAzNi40MTY4IDI0MS4xMTcgMzguMjAxMkMyNDEuMTE3IDM5LjU4MzYgMjQwLjc5IDQwLjU3NjMgMjQwLjEzNiA0MS4xNzk1QzIzOS40ODIgNDEuNzgyNyAyMzguMzYzIDQyLjEwOTUgMjM2Ljc3OSA0Mi4xNTk3TDIyOS43OTkgNDIuMzQ4MkMyMjkuOTc1IDQ1Ljk2NzQgMjMxLjU0NyA0Ny43NzcgMjM0LjUxNSA0Ny43NzdaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0yNTEuMzAzIDM1LjM3MzZWNDUuNTUyNkMyNTEuMzAzIDQ3LjEzNiAyNTEuOTIgNDcuOTI3NyAyNTMuMTUyIDQ3LjkyNzdDMjU0LjUxIDQ3LjkyNzcgMjU1Ljc2OCA0Ny4zMTE5IDI1Ni45MjUgNDYuMDgwNEMyNTcuMjc3IDQ2LjA4MDQgMjU3LjU2NiA0Ni4yMzEyIDI1Ny43OTIgNDYuNTMyOEMyNTguMDQ0IDQ2LjgwOTIgMjU4LjE3IDQ3LjE4NjIgMjU4LjE3IDQ3LjY2MzhDMjU4LjE3IDQ4LjY5NDIgMjU3LjU0MSA0OS42NjE5IDI1Ni4yODMgNTAuNTY2N0MyNTUuMDI2IDUxLjQ0NjMgMjUzLjQ2NiA1MS44ODYyIDI1MS42MDUgNTEuODg2MkMyNDkuNzY5IDUxLjg4NjIgMjQ4LjI3MyA1MS40MjEyIDI0Ny4xMTYgNTAuNDkxM0MyNDUuOTU5IDQ5LjU2MTMgMjQ1LjM4IDQ4LjE0MTMgMjQ1LjM4IDQ2LjIzMTJWMzUuMzczNkMyNDQuNDc1IDM1LjM0ODQgMjQzLjc5NiAzNS4xMzQ4IDI0My4zNDMgMzQuNzMyN0MyNDIuODkgMzQuMzA1NCAyNDIuNjY0IDMzLjc3NzYgMjQyLjY2NCAzMy4xNDkzQzI0Mi42NjQgMzIuNTIwOSAyNDIuOTI4IDMyLjAxODMgMjQzLjQ1NiAzMS42NDEzQzI0NC4wNiAzMS43MTY3IDI0NC43MDEgMzEuNzU0NCAyNDUuMzggMzEuNzU0NFYzMC42MjM0QzI0NS4zOCAyOS4zMTY0IDI0NC45NTMgMjguNDM2OCAyNDQuMDk4IDI3Ljk4NDRDMjQ1LjA1MyAyNi45MDM2IDI0Ni4xODUgMjYuMzYzMyAyNDcuNDkzIDI2LjM2MzNDMjQ4LjgyNiAyNi4zNjMzIDI0OS43OTQgMjYuNzI3NyAyNTAuMzk4IDI3LjQ1NjZDMjUxLjAwMiAyOC4xODU0IDI1MS4zMDMgMjkuMzE2NCAyNTEuMzAzIDMwLjg0OTZWMzEuNzU0NEgyNTdDMjU3LjE3NiAzMS45NTU0IDI1Ny4yNjQgMzIuMjA2OCAyNTcuMjY0IDMyLjUwODRDMjU3LjI2NCAzMy4yODc1IDI1Ni44MzcgMzMuOTY2MSAyNTUuOTgxIDM0LjU0NDJDMjU1LjE1MSAzNS4wOTcxIDI1My42OTMgMzUuMzczNiAyNTEuNjA1IDM1LjM3MzZIMjUxLjMwM1oiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTI2OS42NzcgMjcuMjI4OEgyNjcuNTI3VjM4LjEyNDFIMjY5LjY3N0MyNzEuNDEyIDM4LjEyNDEgMjcyLjc0NSAzNy42MzQgMjczLjY3NiAzNi42NTM4QzI3NC42MzIgMzUuNjQ4NCAyNzUuMTEgMzQuMzE2NCAyNzUuMTEgMzIuNjU3NkMyNzUuMTEgMzAuOTczNiAyNzQuNjMyIDI5LjY1NDEgMjczLjY3NiAyOC42OTkxQzI3Mi43NDUgMjcuNzE4OSAyNzEuNDEyIDI3LjIyODggMjY5LjY3NyAyNy4yMjg4Wk0yNjMuNzU0IDIzLjcyMjdIMjcwLjgwOUMyNzAuODg0IDIzLjcyMjcgMjcwLjk2IDIzLjcyMjcgMjcxLjAzNSAyMy43MjI3QzI3NS4xODUgMjMuNzIyNyAyNzguMjY2IDI1LjAwNDUgMjgwLjI3OCAyNy41NjgxQzI4MS4zODUgMjguOTc1NSAyODEuOTM4IDMwLjY3MiAyODEuOTM4IDMyLjY1NzZDMjgxLjkzOCAzNC42NDMxIDI4MS4zODUgMzYuMzI3IDI4MC4yNzggMzcuNzA5NEMyNzguMjQxIDQwLjI5ODEgMjc1LjA4NSA0MS41OTI1IDI3MC44MDkgNDEuNTkyNUgyNjcuNDg5VjQ3LjU4NjhDMjY3LjQ4OSA0OC45NDQgMjY3LjkwNCA0OS44MzYyIDI2OC43MzQgNTAuMjYzNUMyNjcuNzc4IDUxLjM0NDIgMjY2LjYyMSA1MS44ODQ2IDI2NS4yNjMgNTEuODg0NkMyNjIuNzk4IDUxLjg4NDYgMjYxLjU2NiA1MC4zODkxIDI2MS41NjYgNDcuMzk4M1YyNy45ODI4QzI2MS41NjYgMjYuNzAxIDI2MS4xMzggMjUuODMzOSAyNjAuMjgzIDI1LjM4MTVDMjYxLjIxNCAyNC4yNzU2IDI2Mi4zNzEgMjMuNzIyNyAyNjMuNzU0IDIzLjcyMjdaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0yODUuMzMzIDQ3LjRWMzUuNTk5OUMyODUuMzMzIDM0LjI5MyAyODQuOTA2IDMzLjQxMzMgMjg0LjA1MSAzMi45NjA5QzI4NS4wMDcgMzEuODgwMiAyODYuMTYzIDMxLjMzOTggMjg3LjUyMiAzMS4zMzk4QzI4OS43NiAzMS4zMzk4IDI5MC45OTIgMzIuNTQ2MiAyOTEuMjE5IDM0Ljk1OUMyOTIuMjI1IDMyLjU0NjIgMjkzLjg5NyAzMS4zMzk4IDI5Ni4yMzYgMzEuMzM5OEMyOTcuNDE4IDMxLjMzOTggMjk4LjM4NyAzMS42OTE3IDI5OS4xNDEgMzIuMzk1NEMyOTkuOTIxIDMzLjA5OTIgMzAwLjMxMSAzNC4wNDE3IDMwMC4zMTEgMzUuMjIyOUMzMDAuMzExIDM2LjQwNDIgMzAwLjAwOSAzNy4zNDY3IDI5OS40MDUgMzguMDUwNEMyOTguODI3IDM4LjcyOSAyOTcuOTM0IDM5LjA2ODMgMjk2LjcyNyAzOS4wNjgzQzI5Ni41NzYgMzkuMDY4MyAyOTYuMjQ5IDM5LjA0MzIgMjk1Ljc0NiAzOC45OTI5QzI5NS44NDcgMzguNTQwNSAyOTUuODk3IDM4LjI1MTUgMjk1Ljg5NyAzOC4xMjU4QzI5NS44OTcgMzcuNDk3NSAyOTUuNzA4IDM3LjAwNzQgMjk1LjMzMSAzNi42NTU1QzI5NC45NzkgMzYuMjc4NSAyOTQuNTM5IDM2LjA5IDI5NC4wMTEgMzYuMDlDMjkyLjY1MiAzNi4wOSAyOTEuNzM0IDM3LjEyMDUgMjkxLjI1NyAzOS4xODE0VjQ3LjU4ODVDMjkxLjI1NyA0OC45NDU3IDI5MS42NzIgNDkuODM4IDI5Mi41MDEgNTAuMjY1MkMyOTEuNTQ2IDUxLjM0NiAyOTAuMzg5IDUxLjg4NjMgMjg5LjAzMSA1MS44ODYzQzI4Ni41NjYgNTEuODg2MyAyODUuMzMzIDUwLjM5MDkgMjg1LjMzMyA0Ny40WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMzEyLjAwNiAzNC4yMDVDMzA5Ljg0MyAzNC4yMDUgMzA4Ljc2MiAzNi4wOSAzMDguNzYyIDM5Ljg2QzMwOC43NjIgNDAuNDEzIDMwOC43ODcgNDAuOTkxIDMwOC44MzcgNDEuNTk0MkMzMDkuMTM5IDQ2LjU0NTUgMzEwLjUyMiA0OS4wMjExIDMxMi45ODcgNDkuMDIxMUMzMTUuMTUgNDkuMDIxMSAzMTYuMjMxIDQ3LjEyMzYgMzE2LjIzMSA0My4zMjg0QzMxNi4yMzEgNDIuODAwNiAzMTYuMjA2IDQyLjIyMjYgMzE2LjE1NiA0MS41OTQyQzMxNS44MDQgMzYuNjY4MSAzMTQuNDIgMzQuMjA1IDMxMi4wMDYgMzQuMjA1Wk0zMDUuNDc5IDM0LjAxNjVDMzA3LjI2NSAzMi4yMzIxIDMwOS42MDQgMzEuMzM5OCAzMTIuNDk2IDMxLjMzOThDMzE1LjM4OSAzMS4zMzk4IDMxNy43MjggMzIuMjMyMSAzMTkuNTE0IDM0LjAxNjVDMzIxLjI5OSAzNS43NzU5IDMyMi4xOTIgMzguMzAxOCAzMjIuMTkyIDQxLjU5NDJDMzIyLjE5MiA0NC44ODY3IDMyMS4yODcgNDcuNDI1MiAzMTkuNDc2IDQ5LjIwOTZDMzE3LjY5IDUwLjk5NDEgMzE1LjM1MSA1MS44ODYzIDMxMi40NTkgNTEuODg2M0MzMDkuNTkyIDUxLjg4NjMgMzA3LjI2NSA1MC45OTQxIDMwNS40NzkgNDkuMjA5NkMzMDMuNjk0IDQ3LjQyNTIgMzAyLjgwMSA0NC44ODY3IDMwMi44MDEgNDEuNTk0MkMzMDIuODAxIDM4LjMwMTggMzAzLjY5NCAzNS43NzU5IDMwNS40NzkgMzQuMDE2NVoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTMzNi4zMDEgNDguNDU1NkMzMzcuMzA3IDQ4LjQ1NTYgMzM4LjIgNDcuODE0NyAzMzguOTggNDYuNTMyOUMzMzkuNzU5IDQ1LjI1MTEgMzQwLjE0OSA0My42MTc1IDM0MC4xNDkgNDEuNjMxOUMzNDAuMTQ5IDM5LjY0NjQgMzM5Ljc3MiAzOC4xMTMzIDMzOS4wMTcgMzcuMDMyNUMzMzguMjYzIDM1LjkyNjcgMzM3LjM1NyAzNS4zNzM3IDMzNi4zMDEgMzUuMzczN0MzMzUuNjIyIDM1LjM3MzcgMzM0Ljk2OCAzNS41OTk5IDMzNC4zMzkgMzYuMDUyM0MzMzMuNzM2IDM2LjQ3OTYgMzMzLjI0NSAzNy4wNzAyIDMzMi44NjggMzcuODI0MlY0Ni43MjE0QzMzMy43OTggNDcuODc3NiAzMzQuOTQzIDQ4LjQ1NTYgMzM2LjMwMSA0OC40NTU2Wk0zMjYuOTQ1IDU3LjA4ODlWMzUuNTk5OUMzMjYuOTQ1IDM0LjI5MyAzMjYuNTE3IDMzLjQwMDggMzI1LjY2MiAzMi45MjMyQzMyNi42MTggMzEuODY3NiAzMjcuNzc1IDMxLjMzOTggMzI5LjEzMyAzMS4zMzk4QzMzMS4xMiAzMS4zMzk4IDMzMi4zMDIgMzIuMjQ0NiAzMzIuNjc5IDM0LjA1NDJDMzM0LjAzNyAzMi4yNDQ2IDMzNS44NjEgMzEuMzM5OCAzMzguMTUgMzEuMzM5OEMzNDAuNDM4IDMxLjMzOTggMzQyLjMzNyAzMi4yNDQ2IDM0My44NDYgMzQuMDU0MkMzNDUuMzU1IDM1LjgzODcgMzQ2LjExIDM4LjMzOTUgMzQ2LjExIDQxLjU1NjVDMzQ2LjExIDQ0Ljc3MzYgMzQ1LjIwNCA0Ny4yOTk1IDM0My4zOTMgNDkuMTM0MkMzNDEuNTgzIDUwLjk2OSAzMzkuNDk1IDUxLjg4NjMgMzM3LjEzMSA1MS44ODYzQzMzNS41NzIgNTEuODg2MyAzMzQuMTUxIDUxLjQ0NjUgMzMyLjg2OCA1MC41NjY4VjU3LjI3NzRDMzMyLjg2OCA1OC42MDk1IDMzMy4yODMgNTkuNDg5MiAzMzQuMTEzIDU5LjkxNjRDMzMzLjE4MiA2MS4wMjIzIDMzMi4wMjUgNjEuNTc1MiAzMzAuNjQyIDYxLjU3NTJDMzI4LjE3NyA2MS41NzUyIDMyNi45NDUgNjAuMDc5OCAzMjYuOTQ1IDU3LjA4ODlaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0zNTguODk5IDM4Ljk5MjlDMzU5LjM1MiAzOC45NDI3IDM1OS42OTEgMzguODA0NCAzNTkuOTE4IDM4LjU3ODJDMzYwLjE0NCAzOC4zMjY5IDM2MC4yNTcgMzcuODYxOSAzNjAuMjU3IDM3LjE4MzNDMzYwLjI1NyAzNi41MDQ3IDM2MC4wNTYgMzUuODg5IDM1OS42NTQgMzUuMzM2QzM1OS4yNzYgMzQuNzU4IDM1OC43NDggMzQuNDY4OSAzNTguMDY5IDM0LjQ2ODlDMzU3LjM5IDM0LjQ2ODkgMzU2Ljc0OSAzNC44OTYyIDM1Ni4xNDUgMzUuNzUwN0MzNTUuNTQyIDM2LjYwNTMgMzU1LjEzOSAzNy44NDk0IDM1NC45MzggMzkuNDgzTDM1OC44OTkgMzguOTkyOVpNMzU5LjU0MSA0Ny43NzdDMzYxLjYyOCA0Ny43NzcgMzYzLjM3NiA0Ni45ODUzIDM2NC43ODUgNDUuNDAxOUMzNjUuMzYzIDQ1LjY1MzMgMzY1LjY1MiA0Ni4xODExIDM2NS42NTIgNDYuOTg1M0MzNjUuNjUyIDQ4LjE5MTcgMzY0LjkzNSA0OS4zMTAyIDM2My41MDIgNTAuMzQwNkMzNjIuMDY4IDUxLjM3MTEgMzYwLjM4MyA1MS44ODYzIDM1OC40NDYgNTEuODg2M0MzNTUuNDU0IDUxLjg4NjMgMzUzLjExNSA1MS4wNjk1IDM1MS40MjkgNDkuNDM1OEMzNDkuNzY5IDQ3LjgwMjIgMzQ4LjkzOSA0NS40NjQ4IDM0OC45MzkgNDIuNDIzNkMzNDguOTM5IDM5LjM1NzQgMzQ5Ljg1NyAzNi43NDM1IDM1MS42OTMgMzQuNTgyQzM1My41MjkgMzIuNDIwNiAzNTUuODU2IDMxLjMzOTggMzU4LjY3MyAzMS4zMzk4QzM2MC44MzYgMzEuMzM5OCAzNjIuNjIyIDMyLjA0MzYgMzY0LjAzIDMzLjQ1MUMzNjUuNDM4IDM0LjgzMzQgMzY2LjE0MyAzNi40MTY4IDM2Ni4xNDMgMzguMjAxMkMzNjYuMTQzIDM5LjU4MzYgMzY1LjgxNiA0MC41NzYzIDM2NS4xNjIgNDEuMTc5NUMzNjQuNTA4IDQxLjc4MjcgMzYzLjM4OSA0Mi4xMDk1IDM2MS44MDQgNDIuMTU5N0wzNTQuODI1IDQyLjM0ODJDMzU1LjAwMSA0NS45Njc0IDM1Ni41NzMgNDcuNzc3IDM1OS41NDEgNDcuNzc3WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMzcwLjIxNiA0Ny40VjM1LjU5OTlDMzcwLjIxNiAzNC4yOTMgMzY5Ljc4OSAzMy40MTMzIDM2OC45MzQgMzIuOTYwOUMzNjkuODg5IDMxLjg4MDIgMzcxLjA0NiAzMS4zMzk4IDM3Mi40MDQgMzEuMzM5OEMzNzQuNjQzIDMxLjMzOTggMzc1Ljg3NSAzMi41NDYyIDM3Ni4xMDIgMzQuOTU5QzM3Ny4xMDggMzIuNTQ2MiAzNzguNzggMzEuMzM5OCAzODEuMTE5IDMxLjMzOThDMzgyLjMwMSAzMS4zMzk4IDM4My4yNyAzMS42OTE3IDM4NC4wMjQgMzIuMzk1NEMzODQuODA0IDMzLjA5OTIgMzg1LjE5NCAzNC4wNDE3IDM4NS4xOTQgMzUuMjIyOUMzODUuMTk0IDM2LjQwNDIgMzg0Ljg5MiAzNy4zNDY3IDM4NC4yODggMzguMDUwNEMzODMuNzEgMzguNzI5IDM4Mi44MTcgMzkuMDY4MyAzODEuNjEgMzkuMDY4M0MzODEuNDU5IDM5LjA2ODMgMzgxLjEzMiAzOS4wNDMyIDM4MC42MjkgMzguOTkyOUMzODAuNzI5IDM4LjU0MDUgMzgwLjc4IDM4LjI1MTUgMzgwLjc4IDM4LjEyNThDMzgwLjc4IDM3LjQ5NzUgMzgwLjU5MSAzNy4wMDc0IDM4MC4yMTQgMzYuNjU1NUMzNzkuODYyIDM2LjI3ODUgMzc5LjQyMiAzNi4wOSAzNzguODkzIDM2LjA5QzM3Ny41MzUgMzYuMDkgMzc2LjYxNyAzNy4xMjA1IDM3Ni4xMzkgMzkuMTgxNFY0Ny41ODg1QzM3Ni4xMzkgNDguOTQ1NyAzNzYuNTU0IDQ5LjgzOCAzNzcuMzg0IDUwLjI2NTJDMzc2LjQyOSA1MS4zNDYgMzc1LjI3MiA1MS44ODYzIDM3My45MTMgNTEuODg2M0MzNzEuNDQ5IDUxLjg4NjMgMzcwLjIxNiA1MC4zOTA5IDM3MC4yMTYgNDcuNFoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTM5NC45MjggMzUuMzczNlY0NS41NTI2QzM5NC45MjggNDcuMTM2IDM5NS41NDUgNDcuOTI3NyAzOTYuNzc3IDQ3LjkyNzdDMzk4LjEzNSA0Ny45Mjc3IDM5OS4zOTMgNDcuMzExOSA0MDAuNTUgNDYuMDgwNEM0MDAuOTAyIDQ2LjA4MDQgNDAxLjE5MSA0Ni4yMzEyIDQwMS40MTcgNDYuNTMyOEM0MDEuNjY5IDQ2LjgwOTIgNDAxLjc5NSA0Ny4xODYyIDQwMS43OTUgNDcuNjYzOEM0MDEuNzk1IDQ4LjY5NDIgNDAxLjE2NiA0OS42NjE5IDM5OS45MDggNTAuNTY2N0MzOTguNjUxIDUxLjQ0NjMgMzk3LjA5MSA1MS44ODYyIDM5NS4yMyA1MS44ODYyQzM5My4zOTQgNTEuODg2MiAzOTEuODk4IDUxLjQyMTIgMzkwLjc0MSA1MC40OTEzQzM4OS41ODQgNDkuNTYxMyAzODkuMDA1IDQ4LjE0MTMgMzg5LjAwNSA0Ni4yMzEyVjM1LjM3MzZDMzg4LjEgMzUuMzQ4NCAzODcuNDIxIDM1LjEzNDggMzg2Ljk2OCAzNC43MzI3QzM4Ni41MTUgMzQuMzA1NCAzODYuMjg5IDMzLjc3NzYgMzg2LjI4OSAzMy4xNDkzQzM4Ni4yODkgMzIuNTIwOSAzODYuNTUzIDMyLjAxODMgMzg3LjA4MSAzMS42NDEzQzM4Ny42ODUgMzEuNzE2NyAzODguMzI2IDMxLjc1NDQgMzg5LjAwNSAzMS43NTQ0VjMwLjYyMzRDMzg5LjAwNSAyOS4zMTY0IDM4OC41NzggMjguNDM2OCAzODcuNzIzIDI3Ljk4NDRDMzg4LjY3OCAyNi45MDM2IDM4OS44MSAyNi4zNjMzIDM5MS4xMTggMjYuMzYzM0MzOTIuNDUxIDI2LjM2MzMgMzkzLjQxOSAyNi43Mjc3IDM5NC4wMjMgMjcuNDU2NkMzOTQuNjI3IDI4LjE4NTQgMzk0LjkyOCAyOS4zMTY0IDM5NC45MjggMzAuODQ5NlYzMS43NTQ0SDQwMC42MjVDNDAwLjgwMSAzMS45NTU0IDQwMC44ODkgMzIuMjA2OCA0MDAuODg5IDMyLjUwODRDNDAwLjg4OSAzMy4yODc1IDQwMC40NjIgMzMuOTY2MSAzOTkuNjA2IDM0LjU0NDJDMzk4Ljc3NiAzNS4wOTcxIDM5Ny4zMTggMzUuMzczNiAzOTUuMjMgMzUuMzczNkgzOTQuOTI4WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNNDE4LjczMyAzNS41NjIyQzQxOS4wMSAzNC44MDgyIDQxOS4xNDggMzQuMTI5NiA0MTkuMTQ4IDMzLjUyNjRDNDE5LjE0OCAzMi45MjMyIDQxOS4wMSAzMi40MjA2IDQxOC43MzMgMzIuMDE4NEM0MTkuNDM4IDMxLjU2NiA0MjAuMjU1IDMxLjMzOTggNDIxLjE4NiAzMS4zMzk4QzQyMi41MTkgMzEuMzM5OCA0MjMuNTEyIDMxLjg0MjUgNDI0LjE2NiAzMi44NDc4QzQyNC40OTMgMzMuMzUwNSA0MjQuNjU2IDMzLjk0MTEgNDI0LjY1NiAzNC42MTk3QzQyNC42NTYgMzUuMjk4MyA0MjQuNDkzIDM2LjA3NzUgNDI0LjE2NiAzNi45NTcxTDQxNi45NiA1Ni40NDhDNDE1LjcwMyA1OS44NjYyIDQxMy43MTYgNjEuNTc1MiA0MTAuOTk5IDYxLjU3NTJDNDA5Ljc5MiA2MS41NzUyIDQwOC43OTkgNjEuMjYxMSA0MDguMDE5IDYwLjYzMjdDNDA3LjIzOSA2MC4wMDQ0IDQwNi44NSA1OS4yNTA0IDQwNi44NSA1OC4zNzA3QzQwNi44NSA1Ny41MTYyIDQwNy4wNzYgNTYuNzI0NSA0MDcuNTI5IDU1Ljk5NTZDNDA4LjIwOCA1Ni41NDg2IDQwOC44NjIgNTYuODI1IDQwOS40OSA1Ni44MjVDNDEwLjExOSA1Ni44MjUgNDEwLjY3MiA1Ni42MzY1IDQxMS4xNSA1Ni4yNTk1QzQxMS42NTMgNTUuOTA3NyA0MTIuMDE4IDU1LjM5MjQgNDEyLjI0NCA1NC43MTM4TDQxMy4yMjUgNTEuODQ4NkM0MTMuMTUgNTEuODQ4NiA0MTMuMDg3IDUxLjg0ODYgNDEzLjAzNyA1MS44NDg2QzQxMi4wODEgNTEuODQ4NiA0MTEuMTc1IDUxLjQ4NDIgNDEwLjMyIDUwLjc1NTNDNDA5LjQ2NSA1MC4wMDEzIDQwOC44MjQgNDguOTMzMiA0MDguMzk2IDQ3LjU1MDhMNDA0LjgxMiAzNi4wOUM0MDQuNTM2IDM1LjE4NTIgNDA0LjI4NCAzNC41MTkyIDQwNC4wNTggMzQuMDkxOUM0MDMuODU3IDMzLjYzOTUgNDAzLjU1NSAzMy4yNjI1IDQwMy4xNTIgMzIuOTYwOUM0MDQuMjg0IDMxLjg4MDIgNDA1LjQ0MSAzMS4zMzk4IDQwNi42MjMgMzEuMzM5OEM0MDcuODA1IDMxLjMzOTggNDA4LjcyMyAzMS42NzkxIDQwOS4zNzcgMzIuMzU3N0M0MTAuMDMxIDMzLjAzNjMgNDEwLjU0NyAzNC4xMTcxIDQxMC45MjQgMzUuNTk5OUw0MTQuMzk1IDQ4LjM0MjVMNDE4LjczMyAzNS41NjIyWiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMTEyLjgzNCAxOC4zMjgxSDExNC40MDZWNjAuOTI5MUgxMTIuODM0VjE4LjMyODFaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik04Mi42Mzk4IDc4LjQyMTRINTQuMjUzOVY3Mi44NTA4SDc2LjcwNjFDNzYuNzA2MSA3Mi4xMTEgNzYuNzA2MSA3MS42MDQ2IDc2LjcwNjEgNzEuMDk4MkM3Ni43MzQ4IDYxLjcwODQgNzYuNzUxMiA1Mi4zMTg1IDc2LjgwODUgNDIuOTI4N0M3Ni44MTQgNDEuOTUxMyA3Ni41NjgxIDQxLjM3NjcgNzUuNjc0OCA0MC44MDA2QzY2LjM4OSAzNC44MTM4IDU3LjE0MTUgMjguNzY2OCA0Ny45MTMxIDIyLjY4OThDNDcuMTgzNyAyMi4yMDk0IDQ2LjY5NiAyMi4xNDUyIDQ1Ljk3NzYgMjIuNjA3OUMzNi42NDY3IDI4LjYyMzUgMjcuMjk1NCAzNC42MDYzIDE3Ljk4MjMgNDAuNjQ3OEMxNy41MjA2IDQwLjk0ODEgMTcuMDk1OCA0MS43MDcgMTcuMDkzMSA0Mi4yNTU3QzE3LjAzOTggNTIuMzczMSAxNy4wNTQ4IDYyLjQ5MDUgMTcuMDU0OCA3Mi43NTk0SDQyLjQwNjlWNTguNTc1N0g1MC44MDM1Vjc4LjM3MDlIMTEuMDcwNkMxMS4wNzA2IDc3LjY1OTcgMTEuMDcwNiA3Ny4wNDU1IDExLjA3MDYgNzYuNDMxMkMxMS4wNDQ2IDYzLjkxMTUgMTEuMDA1IDUxLjM5MTcgMTEuMDM3OCAzOC44NzE5QzExLjAzOTIgMzguMzIwNCAxMS41ODQyIDM3LjU3NjUgMTIuMDg5NiAzNy4yNTAzQzIxLjcxMTQgMzEuMDE2MyAzMS4zNzU1IDI0Ljg0NTIgNDEuMDA1NSAxOC42MjQ5QzQyLjcxMDIgMTcuNTIzMyA0NC4zMTI1IDE2LjI2MzQgNDUuOTUwMiAxNS4wNjA4QzQ2LjY0MTQgMTQuNTUzMSA0Ny4yMDQyIDE0LjQzNyA0OC4wMjY1IDE0Ljk5MjZDNTkuMzg4NSAyMi42NjI1IDcwLjc4MzMgMzAuMjg2MSA4Mi4xNTYyIDM3LjkzOTZDODIuNDM3NiAzOC4xMjkzIDgyLjc0MjIgMzguNTQ0MyA4Mi43NDM2IDM4Ljg1NTVDODIuNzc2NCA1MS45NDg2IDgyLjc3NjQgNjUuMDQxNyA4Mi43NzUgNzguMTM0OEM4Mi43NzUgNzguMjI3NiA4Mi42OTE3IDc4LjMxOSA4Mi42NDI1IDc4LjQyMjhMODIuNjM5OCA3OC40MjE0WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMjMuMzM5OSA3LjU5MTA1QzI2Ljg2IDcuNTkxMDUgMzAuMjY4MSA3LjYwNjA3IDMzLjY3NjEgNy41NzE5NEMzNC4wODg2IDcuNTY3ODUgMzQuNTQ0OSA3LjM2NzE5IDM0LjkwNDEgNy4xMzY1MUMzOC41NDU3IDQuODA2NDUgNDIuMTgxOSAyLjQ2Njg0IDQ1Ljc5MzUgMC4wOTAzNzdDNDYuNTQ3NSAtMC40MDY0ODMgNDcuMTA4OSAtMC42NTIxODMgNDguMDEwNCAtMC4wNTk3NzNDNjMuMDI3NyA5LjgxODczIDc4LjA3MjQgMTkuNjUzNiA5My4xMTAyIDI5LjUwMDdDOTMuNDUzIDI5LjcyNTkgOTMuNzgzNiAyOS45NzE2IDk0LjE3MDEgMzAuMjQxOUM5My4yMTk0IDMxLjk1MzYgOTIuMjk3NCAzMy42MTYxIDkxLjI4NjYgMzUuNDM3Qzg4Ljg0MjkgMzMuODI1IDg2LjQ2NzUgMzIuMjU2NiA4NC4wODk0IDMwLjY5MjNDNzIuNTk3NiAyMy4xMzE2IDYxLjA2NDkgMTUuNjMyMyA0OS42NTEgNy45NTQxNEM0Ny42MTg0IDYuNTg3NzggNDYuMzgwOSA2LjQ1MjY0IDQ0LjIzMjIgNy44OTY4MUMzMC43ODQ0IDE2LjkzODYgMTcuMTg3NyAyNS43NTc4IDMuNjM4NzcgMzQuNjQ5NEMzLjMzODI2IDM0Ljg0NiAzLjAyODE5IDM1LjAyODkgMi41NzE5NiAzNS4zMTE1QzEuNTg4NDggMzMuNTQ1MiAwLjYzNTA0IDMxLjgzMjEgLTAuMzU5Mzc1IDMwLjA0NjdDNC4zMTIxOSAyNy4wMTY0IDguODkzNiAyNC4wNDIgMTMuNDc3NyAyMS4wNzMyQzE2LjIzMjkgMTkuMjg5MSAxOC45NzQzIDE3LjQ4NDYgMjEuNzYwOSAxNS43NDk3QzIyLjkzNTYgMTUuMDE4IDIzLjUxMDcgMTQuMTYzNSAyMy4zNzY4IDEyLjY5NzVDMjMuMjIyNSAxMC45OTgxIDIzLjM0MTMgOS4yNzI3MyAyMy4zNDEzIDcuNTkxMDVIMjMuMzM5OVoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTUxLjA4NDggNTIuMDEyMkg0Mi41NDQ4QzQyLjQ5MTUgNTEuMjIzMyA0Mi40IDUwLjQ2OTggNDIuMzk3MyA0OS43MTQ5QzQyLjM3NTQgNDMuMTA0MiA0Mi4zNDY3IDM2LjQ5MzUgNDIuMzk3MyAyOS44ODI4QzQyLjQwMTQgMjkuMjgyMiA0Mi45NDkxIDI4LjY4MyA0My4yNTc4IDI4LjA5MDZDNDMuMzAwMiAyOC4wMDg3IDQzLjQyMzEgMjcuOTY3NyA0My41MDc4IDI3LjkwNzdDNDYuOTMwOSAyNS40NzggNDYuOTQzMiAyNS40NjE2IDUwLjMyNjcgMjguMDI1MUM1MC43MDM3IDI4LjMxMDQgNTEuMDQ5MiAyOC45MTc4IDUxLjA1MiAyOS4zNzc4QzUxLjA5NyAzNi44NzAzIDUxLjA4MzQgNDQuMzYyOCA1MS4wODM0IDUyLjAxMjJINTEuMDg0OFoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTY3LjgzMDYgNTYuNzU5OUg1OS4zNTM1VjMzLjgwNDdDNjAuNzYwNCAzNC43MTEgNjIuMDA2MiAzNS43NDE2IDYzLjQyNjggMzYuMzg0NUM2Ni44NDcxIDM3LjkzMjQgNjguMjk1MSA0MC4yNTg0IDY3LjkxOTQgNDQuMTcwNUM2Ny41MjQ3IDQ4LjI4NDYgNjcuODMwNiA1Mi40NjcgNjcuODMwNiA1Ni43NTk5WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMzQuMjY2MSA1Ni43MjM1SDI1LjgzNjhDMjUuODM2OCA1MC45ODM3IDI1LjgzIDQ1LjMxNDggMjUuODU0NiAzOS42NDZDMjUuODU1OSAzOS4zMzA3IDI2LjAzMjEgMzguODgyOSAyNi4yNzUzIDM4LjcyMzJDMjguODc0NyAzNy4wMTgzIDMxLjUwNDIgMzUuMzU4NSAzNC4yNjQ3IDMzLjU5NzdWNTYuNzI0OEwzNC4yNjYxIDU2LjcyMzVaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik01OS4zNDc3IDU4LjU3ODFINjcuNjcxOFY2Ny4zNzAxSDU5LjM0NzdWNTguNTc4MVoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTI2LjAyOTMgNjcuMzc1N1Y1OC42NjAySDM0LjA1OTdWNjcuMzc1N0gyNi4wMjkzWiIgZmlsbD0iI0YzMDA1MSIvPgo8L2c+CjxkZWZzPgo8Y2xpcFBhdGggaWQ9ImNsaXAwXzExNjBfMzA3NCI+CjxyZWN0IHdpZHRoPSI0MjUiIGhlaWdodD0iNzgiIGZpbGw9IndoaXRlIi8+CjwvY2xpcFBhdGg+CjwvZGVmcz4KPC9zdmc+Cg==" data-filename="logo.f88f19f0.svg"> </h1>
    <h4 style="text-align: center;"><b>LEASE AGREEMENT - RESIDENTIAL</b></h4>
    <center>
        <small>This is a written contract that sets out the terms and conditions between the
            Landlord and Tenant of a
            residential property.</small>
    </center>
    <br>
    <ol>
        <li><b>THE LANDLORD</b>
            <table style="width: 100%; margin: 0; border:none !important;">
                <tr>
                    <td style="width: 25%;">Name & Surname:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;">ID Number:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;">Address:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"> <small>(the address acts as the domicilium citandi et
                            executandi)</small></td>
                </tr>
            </table>
            <br>
            <table style="width: 100%; margin: 0; border:none !important;">
                <tr>
                    <td style="width: 25%;">Email:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;">Cellphone Number:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
            </table>
            <hr>
            <p><b>THE TENANT</b></p>
            <table style="width: 100%; margin: 0; border:none !important;">
                <tr>
                    <td style="width: 25%;">Name & Surname:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;">ID Number:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;">Address:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;"></td>
                    <td style="width: 75%;"> <small>(the address acts as the domicilium citandi et
                            executandi)</small></td>
                </tr>
            </table>
            </p>
            <br>
            <table style="width: 100%; margin: 0; border:none !important;">
                <tr>
                    <td style="width: 25%;">Email:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
                <tr>
                    <td style="width: 25%;">Cellphone Number:</td>
                    <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                </tr>
            </table>
        </li>
        <hr>
        <li><b>THE RESIDENTIAL PREMISES</b>
            <ol>
                <li>The Landlord lets to the Tenant, who hires the following Premises:
                    <table style="width: 100%; margin: 0; border:none !important;">
                        <tr>
                            <td style="width: 15%;">Address:</td>
                            <td style="width: 85%;"><span class="input_field" placeholder=""></span></td>
                        </tr>
                        <tr>
                            <td style="width: 15%;"></td>
                            <td style="width: 85%;"><span class="input_field" placeholder=""></span></td>
                        </tr>
                        <tr>
                            <td style="width: 15%;"></td>
                            <td style="width: 85%;"><span class="input_field" placeholder=""></span></td>
                        </tr>
                    </table>

                </li>
                <li>The Premises does not only refer to the dwelling or grounds let in terms of this agreement, but also
                    to any of the Landlord's fixtures and fittings in/on the dwelling. This includes, but is not limited
                    to keys, locks, windows, electrical appliances, sanitary ware, sewage pipes, stoves, geysers, taps
                    and other fixtures of fittings specifically specified in this agreement.</li>
                <li>
                    The Premises will only be used for private residential purposes, and the Tenant will not allow
                    more than people to stay on the Premises at any time, without obtaining written permission from
                    the Landlord first.
                </li>
                <li>No animals, birds or pets may be kept on the Premises, without written permission of the Landlord.
                </li>
            </ol>
        </li>
        <hr>
        <li><b>RENTAL</b>
            <ol>
                <li>
                    <p>The Tenant agrees to pay the following monthly rental amount:
                        R <span class="input_field w20" placeholder="Amount"></span> <span class="input_field w50"
                            placeholder="Insert amount in words"></span> <small>*count in
                            words</small>.
                    </p>
                </li>
                <li>The rental amount must be paid in advance, free of bank charges, on or before the the first working
                    day of every month.</li>
                <li>Rental payments received after the 7th day of the month, will incur a surcharge of R 250.00 to cover
                    additional administration costs.</li>
                <li>Rental must be paid without deduction or set off, directly into the following bank account:</li>
                <table style="width: 100%; margin: 0; border:none !important;">
                    <tr>
                        <td style="width: 25%;">Bank :</td>
                        <td style="width: 75%;"></td>
                    </tr>
                    <tr>
                        <td style="width: 25%;">Branch Code :</td>
                        <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                    </tr>
                    <tr>
                        <td style="width: 25%;">Account Holder : </td>
                        <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                    </tr>
                    <tr>
                        <td style="width: 25%;">Account Type : </td>
                        <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                    </tr>
                    <tr>
                        <td style="width: 15%;">Account Number : </td>
                        <td style="width: 75%;"><span class="input_field" placeholder=""></span></td>
                    </tr>
                </table>
                <li>
                    <p>The rental amount will be increased every year on the anniversary of the start of this agreement,
                        in the amount of R <span class="input_field w20" placeholder="Amount"></span><span
                            class="input_field w50" placeholder="Insert amount in words"></span> <small>*count in
                            words</small>
                    </p>
                </li>
                <li>A dated receipt will be issued by the Landlord upon receipt of the rental amount. The receipt will
                    indicate the amount received, any arrears due, deposit kept, repair costs and any other charges
                    incurred during the period for which the rental amount was paid.</li>
            </ol>
        </li>
        <hr>
        <li><b>COMMENCEMENT & RENTAL PERIOD</b>
            <ol>
                <li>
                    <p>The rental agreement will be valid from the <span class="input_field w10"
                            placeholder="Day"></span> of <span class="input_field w20" placeholder="Month"></span> 20
                        <span class="input_field w10" placeholder="Year"></span> until <span class="input_field w10"
                            placeholder="Day"></span> of <span class="input_field w20" placeholder="Month"></span> 20
                        <span class="input_field w10" placeholder="Year"></span>.
                    </p>
                </li>
                <li>The lease agreement will automatically be renewed, unless cancelled in writing by either the
                    Landlord or Tenant.
                    Notice of cancellation must be given at least 2 (two) months before the lease expires.</li>
            </ol>
        </li>
        <hr>
        <li><b>DEPOSIT</b>
            <ol>
                <li>
                    <p>The Tenant must as security for fulfllment of all its obligations under this agreement, pay a
                        deposit of R <span class="input_field w20" placeholder="Amount"></span> <span
                            class="input_field w50" placeholder="Insert amount in words"></span> , upon signing this
                        agreement.
                    </p>
                </li>
                <li>The Landlord will keep the deposit in an interest bearing account, for the benefit of the Tenant.
                </li>
                <li>The Tenant cannot apply the deposit as payment of the last months rental or any other rental due to
                    the Landlord.</li>
                <li>The Landlord may deduct amounts payable under this agreement, which remain unpaid after the due
                    date,from the deposit. Should such a deduction be made, the Landlord may request the Tenant to
                    immediately pay an amount to reinstate the deposit to its full amount. The Landlord is further
                    entitled to deduct reasonable costs associated with repair of damages caused to the Premises during
                    the lease period, or the cost for replacing lost keys.</li>
                <li>The balance of the deposit and interest earned, must be refunded to the Tenant within 14 (fourteen)
                    business days after the end of the lease agreement.
                </li>
                <li>If no amounts are due and owing at the end of this lease agreement, the deposit and interest must be
                    refunded to the Tenant, in full, within 7 (seven) business days after the end of the lease
                    agreement.</li>
                <li>If the lease agreement is automatically renewed, the lease will continue on the same conditions
                    contained in this document, but rental will be negotiated. The Landlord may also increase the
                    deposit amount to be equal to the newly negotiated rental.
                </li>
            </ol>
        </li>
        <hr>
        <li><b>INCOMING/OUTGOING INSPECTIONS</b>
            <ol>
                <li>The Landlord and Tenant must jointly inspect the Premises before the Tenant moves in. Should the
                    Tenant fail to meet the Landlord on the mutually agreed date and time to inspect the the Premises,
                    the Premises will be regarded to be free of any defects and damages. A list of defects or damage
                    present must be attached to this agreement. The Tenant must inform the Landlord of any additional
                    defects or damages noted within 7 (Seven) days of moving into the Premises.</li>
                <li>The Premises is let as is, VOETSTOOTS, and the Tenant acknowledges that the Premises is in a good
                    state/condition, suitable for the purposes of letting in terms of this agreement.</li>
                <li>6.3. The Landlord and Tenant must jointly inspect the Premises within <b>5 (five) days </b>of this
                    agreement expiring, to determine if there are any defects or damages causes to the Premises during
                    the lease period.</li>
                <li>The Tenant will be liable for any damages or defects in the Premises, whether visible or concealed
                    during
                    the inspection. Upon termination of the lease agreement, the Tenant must restore the Premises to the
                    Landlord in
                    the same condition it was received at the start of the lease (fair wear and tear excluded).</li>
                <li>Should the Landlord fail to inspect the Premises with the Tenant, the Landlord will be regarded to
                    have acknowledged that the Premises is in a good and proper state of repair, and will have no claim
                    against the
                    Tenant.</li>
                <li>Should the Tenant fail to respond to the Landlord's request to conduct a joint inspection, the
                    Landlord must
                    at the end of the lease, inspect the Premises, within <b>7 (seven) business days</b> from the date
                    that the Tenant
                    moved out of the Premises.
                </li>
            </ol>
        </li>
        <hr>
        <li><b>LANDLORD'S OTHER RESPONSIBILITIES</b>
            <ol>
                <li>The Landlord must provide the Tenant with vacant occupation of the Premises at the start of this
                    lease,
                    and allow the Tenant undisturbed enjoyment of the Premises for the duration of the lease.</li>
                <li>It is the Landlord's responsibility to maintain the exterior, roof, gutters, down pipes of the
                    Premises, in
                    good order and condition (fair wear and tear to be expected).</li>
                <li>The Landlord will further be responsible for maintenance to and repairs of the installations in the
                    Premises including the locks, windows, geysers or other fixtures, fitting and installations. If
                    repairs are required
                    due to fault on the part of the Tenant, the Tenant will be responsible for the necessary repairs or
                    replacements.
                </li>
                <li>The Landlord is responsible for payment of the municipal rates and taxes on the Premises.</li>
            </ol>
        </li>
        <hr>
        <li><b>TENANT'S OTHER RESPONSIBILITIES</b>
            <ol>
                <li>For the duration of the lease, the Tenant must inform the Landlord of any defects or damages that
                    require
                    repair and are the Landlord's responsibility.</li>
                <li> The Tenant is responsible for payment of ordinary consumption charges levied on the Premises, such
                    as water and
                    electricity, sanitary, sewerage and refuse removal services that are not included in the annual
                    rates and taxes.
                </li>
                <li>The interior must be maintained at the Tenant's own cost, in the same good, defect-free condition as
                    it was when
                    the lease started (fair wear and tear excluded). If the Tenant fails to full fill this
                    responsibility, the Landlord
                    may make the necessary repairs or maintenance and recover the costs from the Tenant. Proof of cost
                    will be provided
                    to the Tenant. </li>
                <li>Any broken window glass or mirrors must be replaced by the Tenant at its own cost.</li>
                <li>The Tenant may not drive any nails or object into the walls or ceilings of the Premises, unless the
                    Landlord
                    has provided prior written permission.</li>
                <li>All light bulbs, switches, sockets, locks and keys must be replaced at the Tenant's own cost. The
                    Tenant may
                    not interfere with or overload the electrical, lighting or heating installations of the Premises.
                </li>
                <li>No additional fixtures or fittings may be installed on the Premises without the prior written
                    permission of
                    the Landlord. Consent will not be unreasonably refused. Approved fixtures and fittings may be
                    removed by the Tenant
                    before the end of the lease period. After the lease has expired, all fixtures and fittings which
                    were not removed,
                    become the Landlord's property, and it will not provide any compensation. </li>
                <li>The Tenant will repair any damages or blemishes caused to the Premises upon removal of the Tenant's
                    fixtures and
                    fittings.</li>
                <li>No structural changes or additions may be made, unless the Landlord's prior written permission has
                    been obtained. All alterations, additions and improvements to the Premises become the property of
                    the Landlord and
                    no compensation will be paid.</li>
                <li>The Tenant will not take any action or allow anything that may cause damage to the Premises.
                    Reasonable care
                    will be taken to avoid blockages (gutter, down pipes, sewerage pipes, water pipes, drains). Removal
                    of any
                    blockages will be for the Tenants own pocket.</li>
                <li>The gardens must be kept clean and tidy and in good appearance. Plants must be watered and
                    replaced regularly.
                    All rubbish and litter must be removed and the refuse area kept tidy and sanitary at all times.</li>
                <li>No part of the Premises may be sub-let to or occupied by another person. The Tenant may not assign
                    this
                    lease, nor ceded any of its rights herein, nor part with possession without prior written permission
                    from the
                    Landlord.</li>
                <li>The Tenant may not refuse the Landlord or any of its agents reasonable access to inspect or attend
                    to
                    repairs on the Premises.</li>
                <li><b>1 (one) month</b> prior to the lease expiring, the Tenant must allow the Landlord to display a
                    “To Let”
                    notice and at all reasonable times allow the Landlord or its agent to show prospective tenants the
                    interior of the
                    Premises.</li>
                <li>The Tenant must allow the Landlord to display a “For Sale” notice at any point during the lease, and
                    at
                    all reasonable times allow the Landlord or its agent to show prospective buyers the interior of the
                    Premises.</li>
                <li>All sectional title and body corporate rules must be complied with. The Tenant must also comply with
                    the Landlord's house rules, which may be amended in writing from time to time. A copy will be
                    provided to the
                    Tenant.</li>
                <li>The Tenant must not cause any nuisance to others in the neighborhood.</li>
                <br>
                <li>The Tenant may not keep any illegal substances or weapons on the Premises, nor omit to do anything
                    or keep or
                    do anything on the Premises that may be contrary the terms and conditions of any insurance policy
                    held by the
                    Landlord in respect of the building or Premises.
                </li>
                <li>No pets or animals may be kept on the Premises, without prior written permission from the Landlord.
                </li>
                <li>Prior to vacating the Premises, all fitted carpets must be cleaned by a professional carpet cleaner
                    at
                    the Tenant's own cost.</li>
                <li>The Tenant must return to the Landlord all keys, remote controls and other security items that allow
                    access
                    to the Premises.</li>
                <li>Any repairs or replacements that are the Tenant's responsibility, must be carried out to the
                    satisfaction of
                    the Landlord, by competent and experienced workmen. No inferior or bad quality products may be used
                    at any stage.
                </li>
            </ol>
        </li>
        <hr>
        <li><b>INSURANCE & DAMAGES</b>
            <ol>
                <li>The Tenant must at all times comply with the terms of any insurance policies that the Landlord has
                    for
                    the Premises (building/property). Should the premium increase due to the Tenant's failure to comply,
                    it will be
                    responsible for payment of any additional premium.
                </li>
                <li>The Tenant is responsible for its own household, car or other insurance, protecting its goods while
                    residing on
                    the Premises.</li>
                <li>The Landlord has no responsibility or liability towards the Tenant for any loss, theft or damage to
                    the
                    Tenant's household articles kept on the Premises.
                </li>
                <li>Any temporary interruption in water or electricity supply that may cause the Tenant loss of
                    beneficial
                    occupation does not entitle the Tenant to cancel the lease agreement. The Landlord will also not be
                    held liable for
                    any loss, damage or inconvenience caused.</li>
            </ol>
        </li>
        <hr>
        <li><b>LIABILITY & INDEMNITY</b>
            <ol>
                <li>The Tenant indemnifies the Landlord for any loss or damage to property or injury to persons suffered
                    on
                    the Premises as a result of any act or omission by the Tenant or its occupants, guests, servants or
                    agents.</li>
                <li>The Tenant is liable for its own act and omissions, as well as that of its guests, servants or
                    agents while
                    they are on or in the Premises.</li>
            </ol>
        </li>
        <hr>
        <li><b>CONSENT TO JURISDICTION OF THE MAGISTRATE'S COURT</b>
            <ol>
                <li>The Landlord and Tenant agree to the jurisdiction of the Magistrate's Court, should any legal
                    actions
                    or proceedings relating this agreement or breach thereof be instituted.</li>
                <li>The Tenant will be liable for all costs incurred by the Landlord on an Attorney and Client scale,
                    including collection commission.</li>
                <li>A certificate signed by a director, secretary of agent of the Landlord, indicating the amount due
                    and owing to
                    the Landlord, will be sufficient and prima facie proof of the amount reflected thereon, for purposes
                    of summary
                    judgement or any other legal proceedings.</li>
            </ol>
        </li>
        <hr>
        <li><b>DAMAGE/DESTRUCTION OF THE PREMISES</b>
            <ol>
                <li>Should the Premises at any time during the lease be so damaged or destroyed that it deprives the
                    Tenant
                    of benefcial use and occupation of the Premises, this agreement will end, and both the Tenant and
                    Landlord will only
                    be liable for its obligations up to such date. Neither Party will have any further claim against
                    each other.
                </li>
                <li>Should the Premises at any time during the lease only be partially damaged, this agreement will
                    remain in force
                    and effect, and the Landlord will as soon as reasonably possible, repair the damage. The Tenant will
                    be entitled to
                    a reduction in the rental amount during such a period. Should a dispute arise about the reduction
                    amount, an
                    independent third party will be consulted to decide an appropriate and fair reduction. The Tenant
                    must continue to
                    pay the full rental amount until a reduction has been determined by the Landlord. After the
                    reduction has been
                    determined, the Landlord will credit the Tenant with such an amount as the Tenant has paid in
                    excess of the reduced
                    rent.</li>
                <li>The Tenant will remain responsible for any damage or destruction caused to the Premises as a result
                    of an
                    act or negligence on part of the Tenant or any person who it's responsible for.</li>
            </ol>
        </li>
        <hr>
        <li><b>ONUS & AGENCY</b>
            <ol>
                <li>Where the Tenant must obtain the written permission of the Landlord in terms of this lease
                    agreement, and it
                    is believed that permission is unreasonably refused, the onus of proof lies with the Tenant.</li>
                <li>The Landlord is entitled to appoint a managing agent to manage this lease on its behalf, and will
                    inform
                    the Tenant of such decision or resulting changes to the lease, in writing.</li>
            </ol>
        </li>
        <hr>
        <li><b> CANCELLATION</b>
            <ol>
                <li>The Tenant may choose to cancel the lease at any time, but must give the Landlord at least <b>20
                        (twenty)
                        business days' </b>written notice.</li>
                <li>Should the Tenant cancel the lease before the lease period expires, it will be liable to pay a
                    reasonable cancellation penalty, calculated as follow:
                </li>
            </ol>
        </li>
        <br>
        <table class="table">
            <thead>
                <tr style="border: 1px solid">
                    <th style="text-align: center; "><b>% LEASE PERIOD REMAINING</b></th>
                    <th style="text-align: center; "><b>NOTICE GIVEN</b></th>
                    <th style="text-align: center; "><b>CANCELLATION FEE TO BE PAID<br> BY TENANT</b></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>> 50%</td>
                    <td>Between 20 and 40 business days</td>
                    <td>3 x monthly rent + all rent and services up to termination + any damages to the premises.</td>
                </tr>
                <tr>
                    <td>> 50%</td>
                    <td>More than 40 business days</td>
                    <td>2,5 x monthly rent + all rent and services up to termination + any damages to the premises.</td>
                </tr>
                <tr>
                    <td>
                        < 50%</td>
                    <td>Between 20 and 40 business days</td>
                    <td>2 x monthly rent + all rent and services up to termination + any damages to the premises.</td>
                </tr>
                <tr>
                    <td>
                        < 50%</td>
                    <td>More than 40 business days</td>
                    <td>1,5 x monthly rent + all rent and services up to termination + any damages to the premises.</td>
                </tr>
            </tbody>
        </table>

        <hr>
        <li><b> BREACH OF AGREEMENT</b>
            <ol>
                <li>Should either the Landlord or Tenant breach any term of this agreement, the other must advise
                    the defaulting
                    party of the breach and give a minimum of 7 (seven) days to rectify the breach.</lip>
                <li> If the Tenant fails to pay any amount due and payable in terms of this agreement on the due date or
                    should the
                    Tenant breach any other term of this agreement and remain in default after notice has been provided
                    to rectify the
                    breach, the Landlord may choose to cancel this agreement by providing notice. If the Landlord
                    chooses to cancel the
                    agreement, any claim it may have of arrear rental or damage or cost due to breach or cancellation,
                    will not be
                    prejudiced.</li>
                <li>If the Landlord cancels this lease agreement, and the Tenant disputes the cancellation and remains
                    in occupation of the Premises, the Tenant will continue to pay the agreed rental amount, and other
                    agreed costs on
                    the due date until the matter has been resolved through mediation, litigation or the Rental Housing
                    Tribunal.</li>
                <li>If the dispute is found in the Landlord's favour, all payments made will be regarded as amounts paid
                    by
                    the Tenant on account of damages suffered by the Landlord. If the dispute is found in favour of the
                    Tenant, all
                    payments made by the Tenant will be regarded as amount paid on account of the rent payable by the
                    Tenant.</li>
            </ol>
        </li>
        <hr>
        <li><b>LANDLORD'S TACIT HYPOTHEC</b>
            <ol>
                <li>The Landlord has the right to use furniture and other goods brought onto the premises for use by the
                    Tenant, as
                    security for rental payments due. The Landlord can have these goods sold by the Sheriff of the Court
                    to recover the
                    unpaid rental.</li>
                <li>The Landlord and Tenant agree that the Landlord's hypothec over the goods brought onto the premises
                    extends to
                    secure all claims that the Landlord may have against the Tenant due to breach of any of the terms
                    and conditions of
                    this agreement.</li>
                <li>If the Tenant has vacated the premises following breach, the movable goods that are subject to the
                    Landlord's hypothec and remain on the premises, may be moved and placed in storage by the Landlord,
                    to allow a new
                    tenant to move in.</li>
            </ol>
        </li>
        <hr>
        <li><b>NOTICES</b>
            <ol>
                <li>All notices to be given by either the Tenant or Landlord, must be hand delivered or sent by way of
                    prepaid registered mail at the addresses refected on the first page and which have been chosen as
                    their domicilium
                    citandi et executandi. to the Tenant at the Premises, and will be regarded to have been received
                    after 4 (Four)
                    days after posting by registered mail.
                </li>
                <li>A notice that was sent by registered mail, will be regarded to have been received within 7 (seven)
                    business days from the date that it was posted. Hand delivered notices must be given to a
                    responsible person during
                    ordinary business hours at the addresses refected on the first page, and will be regarded to have
                    been received.</li>
                <li>The Tenant or Landlord must notify the other of any change to its domicilium citandi et executand
                </li>
            </ol>
        </li>
        <hr>
        <li><b>GENERAL TERMS</b>
            <ol>
                <li>No changes to this agreement will have any effect or be of force, unless it is agreed to in writing
                    by both the Tenant and the Landlord.</li>
                <li>This document represents all the agreed terms and conditions between the Landlord and Tenant, and
                    neither has any right or remedy arising rom an undertaking, warranty or representation that is not
                    contained in this document.
                </li>
                <li>Any relaxation or indulgence by either party in exercising any of its rights in terms of this
                    agreement, does not mean that the right is waived or altered. Any single or partial exercise of any
                    right doesn't preclude any other or future exercise thereof or the exercise of any other right
                    under this agreement.</li>
                <li>Headings have been inserted to the various clauses for ease of reference only, and are not to be
                    taken into account when interpreting the terms of this agreement.</li>
                <li>Words importing the singular also include the plural and the other way around. Words importing any
                    one gender also include the other, and words importing persons also includes corporate entities.
                </li>
                <li>This agreement may be signed in one or more counterparts and the signature of one copy by any other
                    party, has the same effect as if that party signed the same document as the other party.</li>
            </ol>
        </li>
        <hr>
        <li><b>SIGNATURES</b>
            <table style="width: 100%; margin: 20px 0; border-radius: 8px; padding: 10px;">
                <!-- Signed at (First Instance) -->
                <tr>
                    <td colspan="3" style="text-align: start; font-weight: bold;">
                        <p>Signed at:
                            <span class="input_field w50" placeholder="Place"></span> on
                            <span class="input_field w10" placeholder="Day"></span> of
                            <span class="input_field w30" placeholder="Month"></span> 20
                            <span class="input_field w10" placeholder="Year"></span>.
                        </p>
                    </td>
                </tr>

                <!-- Tenant Row -->
                <tr>
                    <td style="font-weight: bold; width: 25%;">The Tenant:</td>
                    <td style="text-align: center; width:50%">
                        <span class="input_field" placeholder="Enter Name"></span>
                    </td>
                    <td style="font-weight: bold; width: 25%;"></td>
                </tr>

                <!-- Witnesses (First Instance) -->

                <tr>
                    <td style="font-weight: bold; width: 25%;">Witnesses:</td>
                    <td style="text-align: center; width:50%">
                        <span class="input_field" placeholder="Enter Name"></span>
                    </td>
                    <td style="font-weight: bold; width: 25%;">Witness 1</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; width: 25%;"></td>
                    <td style="text-align: center; width:50%">
                        <span class="input_field" placeholder="Enter Name"></span>
                    </td>
                    <td style="font-weight: bold; width: 25%;">Witness 2</td>
                </tr>


                <!-- Signed at (Second Instance) -->
                <tr>
                    <td colspan="3" style="text-align: start; font-weight: bold;">
                        <p>Signed at:
                            <span class="input_field w50" placeholder="Place"></span> on
                            <span class="input_field w10" placeholder="Day"></span> of
                            <span class="input_field w30" placeholder="Month"></span> 20
                            <span class="input_field w10" placeholder="Year"></span>.
                        </p>
                    </td>
                </tr>

                <!-- Landlord Row -->
                <tr>
                    <td style="font-weight: bold; width: 25%;">The Landlord:</td>
                    <td style="text-align: center; width:50%">
                        <span class="input_field" placeholder="Enter Name"></span>
                    </td>
                    <td style="font-weight: bold; width: 25%;"></td>
                </tr>
                <tr>
                    <td style="font-weight: bold; width: 25%;">Witnesses:</td>
                    <td style="text-align: center; width:50%">
                        <span class="input_field" placeholder="Enter Name"></span>
                    </td>
                    <td style="font-weight: bold; width: 25%;">Witness 1</td>
                </tr>
                <tr>
                    <td style="font-weight: bold; width: 25%;"></td>
                    <td style="text-align: center; width:50%">
                        <span class="input_field" placeholder="Enter Name"></span>
                    </td>
                    <td style="font-weight: bold; width: 25%;">Witness 2</td>
                </tr>

            </table>


            </ul>
        </li>
        <footer>
            <hr>
            <p>
                <b>DISCLAIMER</b>
            </p>
            <p>This contract template is provided by PocketProperty and is based on a template from Law For All, an initiative dedicated to making legal resources more accessible to South Africans. While we have ensured that the document aligns with current legal standards, South African law is constantly evolving. As such, we cannot guarantee that this template is free from errors, omissions, or outdated information.</p>
            <p>This document is intended as a guideline and should not be considered legal advice. We strongly recommend consulting a qualified attorney before making any legal decisions or signing any agreements. PocketProperty and its affiliates accept no liability for any consequences arising from the use of this template.</p>

            <center style="padding-top:2rem;">
            <img style="width: 425px; border:1px solid #000;padding:2rem;" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDI1IiBoZWlnaHQ9Ijc4IiB2aWV3Qm94PSIwIDAgNDI1IDc4IiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfMTE2MF8zMDc0KSI+CjxwYXRoIGQ9Ik0xNDIuNjE1IDI3LjIyODhIMTQwLjQ2NFYzOC4xMjQxSDE0Mi42MTVDMTQ0LjM1IDM4LjEyNDEgMTQ1LjY4MyAzNy42MzQgMTQ2LjYxNCAzNi42NTM4QzE0Ny41NjkgMzUuNjQ4NCAxNDguMDQ3IDM0LjMxNjQgMTQ4LjA0NyAzMi42NTc2QzE0OC4wNDcgMzAuOTczNiAxNDcuNTY5IDI5LjY1NDEgMTQ2LjYxNCAyOC42OTkxQzE0NS42ODMgMjcuNzE4OSAxNDQuMzUgMjcuMjI4OCAxNDIuNjE1IDI3LjIyODhaTTEzNi42OTIgMjMuNzIyN0gxNDMuNzQ2QzE0My44MjIgMjMuNzIyNyAxNDMuODk3IDIzLjcyMjcgMTQzLjk3MyAyMy43MjI3QzE0OC4xMjMgMjMuNzIyNyAxNTEuMjA0IDI1LjAwNDUgMTUzLjIxNiAyNy41NjgxQzE1NC4zMjIgMjguOTc1NSAxNTQuODc2IDMwLjY3MiAxNTQuODc2IDMyLjY1NzZDMTU0Ljg3NiAzNC42NDMxIDE1NC4zMjIgMzYuMzI3IDE1My4yMTYgMzcuNzA5NEMxNTEuMTc4IDQwLjI5ODEgMTQ4LjAyMiA0MS41OTI1IDE0My43NDYgNDEuNTkyNUgxNDAuNDI2VjQ3LjU4NjhDMTQwLjQyNiA0OC45NDQgMTQwLjg0MSA0OS44MzYyIDE0MS42NzEgNTAuMjYzNUMxNDAuNzE2IDUxLjM0NDIgMTM5LjU1OSA1MS44ODQ2IDEzOC4yMDEgNTEuODg0NkMxMzUuNzM2IDUxLjg4NDYgMTM0LjUwMyA1MC4zODkxIDEzNC41MDMgNDcuMzk4M1YyNy45ODI4QzEzNC41MDMgMjYuNzAxIDEzNC4wNzYgMjUuODMzOSAxMzMuMjIxIDI1LjM4MTVDMTM0LjE1MSAyNC4yNzU2IDEzNS4zMDggMjMuNzIyNyAxMzYuNjkyIDIzLjcyMjdaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0xNjYuMzQ0IDM0LjIwNUMxNjQuMTgxIDM0LjIwNSAxNjMuMDk5IDM2LjA5IDE2My4wOTkgMzkuODZDMTYzLjA5OSA0MC40MTMgMTYzLjEyNSA0MC45OTEgMTYzLjE3NSA0MS41OTQyQzE2My40NzcgNDYuNTQ1NSAxNjQuODYgNDkuMDIxMSAxNjcuMzI1IDQ5LjAyMTFDMTY5LjQ4OCA0OS4wMjExIDE3MC41NjkgNDcuMTIzNiAxNzAuNTY5IDQzLjMyODRDMTcwLjU2OSA0Mi44MDA2IDE3MC41NDQgNDIuMjIyNiAxNzAuNDk0IDQxLjU5NDJDMTcwLjE0MiAzNi42NjgxIDE2OC43NTggMzQuMjA1IDE2Ni4zNDQgMzQuMjA1Wk0xNTkuODE3IDM0LjAxNjVDMTYxLjYwMyAzMi4yMzIxIDE2My45NDIgMzEuMzM5OCAxNjYuODM0IDMxLjMzOThDMTY5LjcyNyAzMS4zMzk4IDE3Mi4wNjYgMzIuMjMyMSAxNzMuODUxIDM0LjAxNjVDMTc1LjYzNyAzNS43NzU5IDE3Ni41MyAzOC4zMDE4IDE3Ni41MyA0MS41OTQyQzE3Ni41MyA0NC44ODY3IDE3NS42MjUgNDcuNDI1MiAxNzMuODE0IDQ5LjIwOTZDMTcyLjAyOCA1MC45OTQxIDE2OS42ODkgNTEuODg2MyAxNjYuNzk3IDUxLjg4NjNDMTYzLjkyOSA1MS44ODYzIDE2MS42MDMgNTAuOTk0MSAxNTkuODE3IDQ5LjIwOTZDMTU4LjAzMiA0Ny40MjUyIDE1Ny4xMzkgNDQuODg2NyAxNTcuMTM5IDQxLjU5NDJDMTU3LjEzOSAzOC4zMDE4IDE1OC4wMzIgMzUuNzc1OSAxNTkuODE3IDM0LjAxNjVaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0xOTYuNzE0IDM1LjgyNjFDMTk2LjcxNCAzNi43NTYxIDE5Ni40MjUgMzcuNDg0OSAxOTUuODQ3IDM4LjAxMjdDMTk1LjI2OCAzOC41MTU0IDE5NC41MDEgMzguNzY2NyAxOTMuNTQ1IDM4Ljc2NjdDMTkyLjU4OSAzOC43NjY3IDE5MS43MzQgMzguNDc3NyAxOTAuOTggMzcuODk5NkMxOTEuMDU1IDM3LjU5OCAxOTEuMDkzIDM3LjE5NTkgMTkxLjA5MyAzNi42OTMyQzE5MS4wOTMgMzYuMTkwNiAxOTAuOTQyIDM1LjczODIgMTkwLjY0IDM1LjMzNkMxOTAuMzY0IDM0LjkzMzkgMTg5Ljk3NCAzNC43MzI4IDE4OS40NzEgMzQuNzMyOEMxODguNDkgMzQuNzMyOCAxODcuNjM1IDM1LjM5ODkgMTg2LjkwNSAzNi43MzA5QzE4Ni4yMDEgMzguMDYzIDE4NS44NDkgMzkuNzIxOCAxODUuODQ5IDQxLjcwNzNDMTg1Ljg0OSA0My42Njc3IDE4Ni4yNzcgNDUuMTc1NyAxODcuMTMyIDQ2LjIzMTNDMTg4LjAxMiA0Ny4yNjE4IDE4OS4xNTYgNDcuNzc3IDE5MC41NjUgNDcuNzc3QzE5Mi42NTIgNDcuNzc3IDE5NC40IDQ2Ljk4NTMgMTk1LjgwOSA0NS40MDE5QzE5Ni4zODcgNDUuNjI4MSAxOTYuNjc2IDQ2LjE1NTkgMTk2LjY3NiA0Ni45ODUzQzE5Ni42NzYgNDguMjY3MSAxOTUuOTYgNDkuNDEwNyAxOTQuNTI2IDUwLjQxNkMxOTMuMTE4IDUxLjM5NjIgMTkxLjQzMyA1MS44ODYzIDE4OS40NzEgNTEuODg2M0MxODYuNTI4IDUxLjg4NjMgMTg0LjIzOSA1MS4wNDQ0IDE4Mi42MDUgNDkuMzYwNEMxODAuOTcgNDcuNjc2NSAxODAuMTUyIDQ1LjM1MTcgMTgwLjE1MiA0Mi4zODU5QzE4MC4xNTIgMzkuNDIwMiAxODEuMTA4IDM2Ljg0NCAxODMuMDIgMzQuNjU3NEMxODQuOTMxIDMyLjQ0NTcgMTg3LjM4MyAzMS4zMzk4IDE5MC4zNzYgMzEuMzM5OEMxOTIuMDYxIDMxLjMzOTggMTkzLjUzMyAzMS43NTQ1IDE5NC43OSAzMi41ODM5QzE5Ni4wNzMgMzMuMzg4MiAxOTYuNzE0IDM0LjQ2ODkgMTk2LjcxNCAzNS44MjYxWiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMjA2LjkzOCAyNi40MDA0VjM5LjEwNTNDMjExLjYxNiAzNi45MTg3IDIxMy45NTUgMzQuMzI5OSAyMTMuOTU1IDMxLjMzOTFDMjE1LjY0IDMxLjMzOTEgMjE2Ljk5OSAzMS42Nzg0IDIxOC4wMyAzMi4zNTdDMjE5LjA2MSAzMy4wMzU2IDIxOS41NzYgMzMuOTkwNiAyMTkuNTc2IDM1LjIyMjJDMjE5LjU3NiAzNi40Mjg2IDIxOC44ODUgMzcuNDk2NyAyMTcuNTAyIDM4LjQyNjdDMjE2LjE0MyAzOS4zNTY2IDIxMy45OTMgNDAuMjYxNCAyMTEuMDUgNDEuMTQxMUMyMTMuNDkgNDMuNjA0MSAyMTUuMzAxIDQ1LjE3NSAyMTYuNDgzIDQ1Ljg1MzZDMjE3LjY2NSA0Ni41MzIyIDIxOC45ODUgNDYuODcxNSAyMjAuNDQ0IDQ2Ljg3MTVDMjIwLjQ0NCA0OC40NTQ5IDIyMC4wNjcgNDkuNjg2NCAyMTkuMzEyIDUwLjU2NjFDMjE4LjU1OCA1MS40NDU3IDIxNy41MTQgNTEuODg1NiAyMTYuMTgxIDUxLjg4NTZDMjE0Ljg0OCA1MS44ODU2IDIxMy40NzcgNTEuMjE5NSAyMTIuMDY5IDQ5Ljg4NzVDMjEwLjY4NiA0OC41MzAzIDIwOC45NzUgNDYuMjA1NCAyMDYuOTM4IDQyLjkxM1Y0Ny41ODc4QzIwNi45MzggNDguOTQ1IDIwNy4zNTMgNDkuODM3MiAyMDguMTgzIDUwLjI2NDVDMjA3LjIyNyA1MS4zNDUyIDIwNi4wNyA1MS44ODU2IDIwNC43MTIgNTEuODg1NkMyMDIuMjQ4IDUxLjg4NTYgMjAxLjAxNSA1MC4zOTAxIDIwMS4wMTUgNDcuMzk5M1YyNi4xNzQyQzIwMS4wMTUgMjQuOTE3NSAyMDAuNTg4IDI0LjAyNTMgMTk5LjczMiAyMy40OTc1QzIwMC42ODggMjIuNDQxOSAyMDEuODQ1IDIxLjkxNDEgMjAzLjIwMyAyMS45MTQxQzIwNS42OTMgMjEuOTE0MSAyMDYuOTM4IDIzLjQwOTUgMjA2LjkzOCAyNi40MDA0WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMjMzLjg3NCAzOC45OTI5QzIzNC4zMjcgMzguOTQyNyAyMzQuNjY2IDM4LjgwNDQgMjM0Ljg5MiAzOC41NzgyQzIzNS4xMTkgMzguMzI2OSAyMzUuMjMyIDM3Ljg2MTkgMjM1LjIzMiAzNy4xODMzQzIzNS4yMzIgMzYuNTA0NyAyMzUuMDMxIDM1Ljg4OSAyMzQuNjI4IDM1LjMzNkMyMzQuMjUxIDM0Ljc1OCAyMzMuNzIzIDM0LjQ2ODkgMjMzLjA0NCAzNC40Njg5QzIzMi4zNjUgMzQuNDY4OSAyMzEuNzIzIDM0Ljg5NjIgMjMxLjEyIDM1Ljc1MDdDMjMwLjUxNiAzNi42MDUzIDIzMC4xMTQgMzcuODQ5NCAyMjkuOTEzIDM5LjQ4M0wyMzMuODc0IDM4Ljk5MjlaTTIzNC41MTUgNDcuNzc3QzIzNi42MDMgNDcuNzc3IDIzOC4zNTEgNDYuOTg1MyAyMzkuNzU5IDQ1LjQwMTlDMjQwLjMzOCA0NS42NTMzIDI0MC42MjcgNDYuMTgxMSAyNDAuNjI3IDQ2Ljk4NTNDMjQwLjYyNyA0OC4xOTE3IDIzOS45MSA0OS4zMTAyIDIzOC40NzYgNTAuMzQwNkMyMzcuMDQzIDUxLjM3MTEgMjM1LjM1OCA1MS44ODYzIDIzMy40MjEgNTEuODg2M0MyMzAuNDI4IDUxLjg4NjMgMjI4LjA4OSA1MS4wNjk1IDIyNi40MDQgNDkuNDM1OEMyMjQuNzQ0IDQ3LjgwMjIgMjIzLjkxNCA0NS40NjQ4IDIyMy45MTQgNDIuNDIzNkMyMjMuOTE0IDM5LjM1NzQgMjI0LjgzMiAzNi43NDM1IDIyNi42NjggMzQuNTgyQzIyOC41MDQgMzIuNDIwNiAyMzAuODMxIDMxLjMzOTggMjMzLjY0NyAzMS4zMzk4QzIzNS44MSAzMS4zMzk4IDIzNy41OTYgMzIuMDQzNiAyMzkuMDA1IDMzLjQ1MUMyNDAuNDEzIDM0LjgzMzQgMjQxLjExNyAzNi40MTY4IDI0MS4xMTcgMzguMjAxMkMyNDEuMTE3IDM5LjU4MzYgMjQwLjc5IDQwLjU3NjMgMjQwLjEzNiA0MS4xNzk1QzIzOS40ODIgNDEuNzgyNyAyMzguMzYzIDQyLjEwOTUgMjM2Ljc3OSA0Mi4xNTk3TDIyOS43OTkgNDIuMzQ4MkMyMjkuOTc1IDQ1Ljk2NzQgMjMxLjU0NyA0Ny43NzcgMjM0LjUxNSA0Ny43NzdaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0yNTEuMzAzIDM1LjM3MzZWNDUuNTUyNkMyNTEuMzAzIDQ3LjEzNiAyNTEuOTIgNDcuOTI3NyAyNTMuMTUyIDQ3LjkyNzdDMjU0LjUxIDQ3LjkyNzcgMjU1Ljc2OCA0Ny4zMTE5IDI1Ni45MjUgNDYuMDgwNEMyNTcuMjc3IDQ2LjA4MDQgMjU3LjU2NiA0Ni4yMzEyIDI1Ny43OTIgNDYuNTMyOEMyNTguMDQ0IDQ2LjgwOTIgMjU4LjE3IDQ3LjE4NjIgMjU4LjE3IDQ3LjY2MzhDMjU4LjE3IDQ4LjY5NDIgMjU3LjU0MSA0OS42NjE5IDI1Ni4yODMgNTAuNTY2N0MyNTUuMDI2IDUxLjQ0NjMgMjUzLjQ2NiA1MS44ODYyIDI1MS42MDUgNTEuODg2MkMyNDkuNzY5IDUxLjg4NjIgMjQ4LjI3MyA1MS40MjEyIDI0Ny4xMTYgNTAuNDkxM0MyNDUuOTU5IDQ5LjU2MTMgMjQ1LjM4IDQ4LjE0MTMgMjQ1LjM4IDQ2LjIzMTJWMzUuMzczNkMyNDQuNDc1IDM1LjM0ODQgMjQzLjc5NiAzNS4xMzQ4IDI0My4zNDMgMzQuNzMyN0MyNDIuODkgMzQuMzA1NCAyNDIuNjY0IDMzLjc3NzYgMjQyLjY2NCAzMy4xNDkzQzI0Mi42NjQgMzIuNTIwOSAyNDIuOTI4IDMyLjAxODMgMjQzLjQ1NiAzMS42NDEzQzI0NC4wNiAzMS43MTY3IDI0NC43MDEgMzEuNzU0NCAyNDUuMzggMzEuNzU0NFYzMC42MjM0QzI0NS4zOCAyOS4zMTY0IDI0NC45NTMgMjguNDM2OCAyNDQuMDk4IDI3Ljk4NDRDMjQ1LjA1MyAyNi45MDM2IDI0Ni4xODUgMjYuMzYzMyAyNDcuNDkzIDI2LjM2MzNDMjQ4LjgyNiAyNi4zNjMzIDI0OS43OTQgMjYuNzI3NyAyNTAuMzk4IDI3LjQ1NjZDMjUxLjAwMiAyOC4xODU0IDI1MS4zMDMgMjkuMzE2NCAyNTEuMzAzIDMwLjg0OTZWMzEuNzU0NEgyNTdDMjU3LjE3NiAzMS45NTU0IDI1Ny4yNjQgMzIuMjA2OCAyNTcuMjY0IDMyLjUwODRDMjU3LjI2NCAzMy4yODc1IDI1Ni44MzcgMzMuOTY2MSAyNTUuOTgxIDM0LjU0NDJDMjU1LjE1MSAzNS4wOTcxIDI1My42OTMgMzUuMzczNiAyNTEuNjA1IDM1LjM3MzZIMjUxLjMwM1oiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTI2OS42NzcgMjcuMjI4OEgyNjcuNTI3VjM4LjEyNDFIMjY5LjY3N0MyNzEuNDEyIDM4LjEyNDEgMjcyLjc0NSAzNy42MzQgMjczLjY3NiAzNi42NTM4QzI3NC42MzIgMzUuNjQ4NCAyNzUuMTEgMzQuMzE2NCAyNzUuMTEgMzIuNjU3NkMyNzUuMTEgMzAuOTczNiAyNzQuNjMyIDI5LjY1NDEgMjczLjY3NiAyOC42OTkxQzI3Mi43NDUgMjcuNzE4OSAyNzEuNDEyIDI3LjIyODggMjY5LjY3NyAyNy4yMjg4Wk0yNjMuNzU0IDIzLjcyMjdIMjcwLjgwOUMyNzAuODg0IDIzLjcyMjcgMjcwLjk2IDIzLjcyMjcgMjcxLjAzNSAyMy43MjI3QzI3NS4xODUgMjMuNzIyNyAyNzguMjY2IDI1LjAwNDUgMjgwLjI3OCAyNy41NjgxQzI4MS4zODUgMjguOTc1NSAyODEuOTM4IDMwLjY3MiAyODEuOTM4IDMyLjY1NzZDMjgxLjkzOCAzNC42NDMxIDI4MS4zODUgMzYuMzI3IDI4MC4yNzggMzcuNzA5NEMyNzguMjQxIDQwLjI5ODEgMjc1LjA4NSA0MS41OTI1IDI3MC44MDkgNDEuNTkyNUgyNjcuNDg5VjQ3LjU4NjhDMjY3LjQ4OSA0OC45NDQgMjY3LjkwNCA0OS44MzYyIDI2OC43MzQgNTAuMjYzNUMyNjcuNzc4IDUxLjM0NDIgMjY2LjYyMSA1MS44ODQ2IDI2NS4yNjMgNTEuODg0NkMyNjIuNzk4IDUxLjg4NDYgMjYxLjU2NiA1MC4zODkxIDI2MS41NjYgNDcuMzk4M1YyNy45ODI4QzI2MS41NjYgMjYuNzAxIDI2MS4xMzggMjUuODMzOSAyNjAuMjgzIDI1LjM4MTVDMjYxLjIxNCAyNC4yNzU2IDI2Mi4zNzEgMjMuNzIyNyAyNjMuNzU0IDIzLjcyMjdaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0yODUuMzMzIDQ3LjRWMzUuNTk5OUMyODUuMzMzIDM0LjI5MyAyODQuOTA2IDMzLjQxMzMgMjg0LjA1MSAzMi45NjA5QzI4NS4wMDcgMzEuODgwMiAyODYuMTYzIDMxLjMzOTggMjg3LjUyMiAzMS4zMzk4QzI4OS43NiAzMS4zMzk4IDI5MC45OTIgMzIuNTQ2MiAyOTEuMjE5IDM0Ljk1OUMyOTIuMjI1IDMyLjU0NjIgMjkzLjg5NyAzMS4zMzk4IDI5Ni4yMzYgMzEuMzM5OEMyOTcuNDE4IDMxLjMzOTggMjk4LjM4NyAzMS42OTE3IDI5OS4xNDEgMzIuMzk1NEMyOTkuOTIxIDMzLjA5OTIgMzAwLjMxMSAzNC4wNDE3IDMwMC4zMTEgMzUuMjIyOUMzMDAuMzExIDM2LjQwNDIgMzAwLjAwOSAzNy4zNDY3IDI5OS40MDUgMzguMDUwNEMyOTguODI3IDM4LjcyOSAyOTcuOTM0IDM5LjA2ODMgMjk2LjcyNyAzOS4wNjgzQzI5Ni41NzYgMzkuMDY4MyAyOTYuMjQ5IDM5LjA0MzIgMjk1Ljc0NiAzOC45OTI5QzI5NS44NDcgMzguNTQwNSAyOTUuODk3IDM4LjI1MTUgMjk1Ljg5NyAzOC4xMjU4QzI5NS44OTcgMzcuNDk3NSAyOTUuNzA4IDM3LjAwNzQgMjk1LjMzMSAzNi42NTU1QzI5NC45NzkgMzYuMjc4NSAyOTQuNTM5IDM2LjA5IDI5NC4wMTEgMzYuMDlDMjkyLjY1MiAzNi4wOSAyOTEuNzM0IDM3LjEyMDUgMjkxLjI1NyAzOS4xODE0VjQ3LjU4ODVDMjkxLjI1NyA0OC45NDU3IDI5MS42NzIgNDkuODM4IDI5Mi41MDEgNTAuMjY1MkMyOTEuNTQ2IDUxLjM0NiAyOTAuMzg5IDUxLjg4NjMgMjg5LjAzMSA1MS44ODYzQzI4Ni41NjYgNTEuODg2MyAyODUuMzMzIDUwLjM5MDkgMjg1LjMzMyA0Ny40WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMzEyLjAwNiAzNC4yMDVDMzA5Ljg0MyAzNC4yMDUgMzA4Ljc2MiAzNi4wOSAzMDguNzYyIDM5Ljg2QzMwOC43NjIgNDAuNDEzIDMwOC43ODcgNDAuOTkxIDMwOC44MzcgNDEuNTk0MkMzMDkuMTM5IDQ2LjU0NTUgMzEwLjUyMiA0OS4wMjExIDMxMi45ODcgNDkuMDIxMUMzMTUuMTUgNDkuMDIxMSAzMTYuMjMxIDQ3LjEyMzYgMzE2LjIzMSA0My4zMjg0QzMxNi4yMzEgNDIuODAwNiAzMTYuMjA2IDQyLjIyMjYgMzE2LjE1NiA0MS41OTQyQzMxNS44MDQgMzYuNjY4MSAzMTQuNDIgMzQuMjA1IDMxMi4wMDYgMzQuMjA1Wk0zMDUuNDc5IDM0LjAxNjVDMzA3LjI2NSAzMi4yMzIxIDMwOS42MDQgMzEuMzM5OCAzMTIuNDk2IDMxLjMzOThDMzE1LjM4OSAzMS4zMzk4IDMxNy43MjggMzIuMjMyMSAzMTkuNTE0IDM0LjAxNjVDMzIxLjI5OSAzNS43NzU5IDMyMi4xOTIgMzguMzAxOCAzMjIuMTkyIDQxLjU5NDJDMzIyLjE5MiA0NC44ODY3IDMyMS4yODcgNDcuNDI1MiAzMTkuNDc2IDQ5LjIwOTZDMzE3LjY5IDUwLjk5NDEgMzE1LjM1MSA1MS44ODYzIDMxMi40NTkgNTEuODg2M0MzMDkuNTkyIDUxLjg4NjMgMzA3LjI2NSA1MC45OTQxIDMwNS40NzkgNDkuMjA5NkMzMDMuNjk0IDQ3LjQyNTIgMzAyLjgwMSA0NC44ODY3IDMwMi44MDEgNDEuNTk0MkMzMDIuODAxIDM4LjMwMTggMzAzLjY5NCAzNS43NzU5IDMwNS40NzkgMzQuMDE2NVoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTMzNi4zMDEgNDguNDU1NkMzMzcuMzA3IDQ4LjQ1NTYgMzM4LjIgNDcuODE0NyAzMzguOTggNDYuNTMyOUMzMzkuNzU5IDQ1LjI1MTEgMzQwLjE0OSA0My42MTc1IDM0MC4xNDkgNDEuNjMxOUMzNDAuMTQ5IDM5LjY0NjQgMzM5Ljc3MiAzOC4xMTMzIDMzOS4wMTcgMzcuMDMyNUMzMzguMjYzIDM1LjkyNjcgMzM3LjM1NyAzNS4zNzM3IDMzNi4zMDEgMzUuMzczN0MzMzUuNjIyIDM1LjM3MzcgMzM0Ljk2OCAzNS41OTk5IDMzNC4zMzkgMzYuMDUyM0MzMzMuNzM2IDM2LjQ3OTYgMzMzLjI0NSAzNy4wNzAyIDMzMi44NjggMzcuODI0MlY0Ni43MjE0QzMzMy43OTggNDcuODc3NiAzMzQuOTQzIDQ4LjQ1NTYgMzM2LjMwMSA0OC40NTU2Wk0zMjYuOTQ1IDU3LjA4ODlWMzUuNTk5OUMzMjYuOTQ1IDM0LjI5MyAzMjYuNTE3IDMzLjQwMDggMzI1LjY2MiAzMi45MjMyQzMyNi42MTggMzEuODY3NiAzMjcuNzc1IDMxLjMzOTggMzI5LjEzMyAzMS4zMzk4QzMzMS4xMiAzMS4zMzk4IDMzMi4zMDIgMzIuMjQ0NiAzMzIuNjc5IDM0LjA1NDJDMzM0LjAzNyAzMi4yNDQ2IDMzNS44NjEgMzEuMzM5OCAzMzguMTUgMzEuMzM5OEMzNDAuNDM4IDMxLjMzOTggMzQyLjMzNyAzMi4yNDQ2IDM0My44NDYgMzQuMDU0MkMzNDUuMzU1IDM1LjgzODcgMzQ2LjExIDM4LjMzOTUgMzQ2LjExIDQxLjU1NjVDMzQ2LjExIDQ0Ljc3MzYgMzQ1LjIwNCA0Ny4yOTk1IDM0My4zOTMgNDkuMTM0MkMzNDEuNTgzIDUwLjk2OSAzMzkuNDk1IDUxLjg4NjMgMzM3LjEzMSA1MS44ODYzQzMzNS41NzIgNTEuODg2MyAzMzQuMTUxIDUxLjQ0NjUgMzMyLjg2OCA1MC41NjY4VjU3LjI3NzRDMzMyLjg2OCA1OC42MDk1IDMzMy4yODMgNTkuNDg5MiAzMzQuMTEzIDU5LjkxNjRDMzMzLjE4MiA2MS4wMjIzIDMzMi4wMjUgNjEuNTc1MiAzMzAuNjQyIDYxLjU3NTJDMzI4LjE3NyA2MS41NzUyIDMyNi45NDUgNjAuMDc5OCAzMjYuOTQ1IDU3LjA4ODlaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik0zNTguODk5IDM4Ljk5MjlDMzU5LjM1MiAzOC45NDI3IDM1OS42OTEgMzguODA0NCAzNTkuOTE4IDM4LjU3ODJDMzYwLjE0NCAzOC4zMjY5IDM2MC4yNTcgMzcuODYxOSAzNjAuMjU3IDM3LjE4MzNDMzYwLjI1NyAzNi41MDQ3IDM2MC4wNTYgMzUuODg5IDM1OS42NTQgMzUuMzM2QzM1OS4yNzYgMzQuNzU4IDM1OC43NDggMzQuNDY4OSAzNTguMDY5IDM0LjQ2ODlDMzU3LjM5IDM0LjQ2ODkgMzU2Ljc0OSAzNC44OTYyIDM1Ni4xNDUgMzUuNzUwN0MzNTUuNTQyIDM2LjYwNTMgMzU1LjEzOSAzNy44NDk0IDM1NC45MzggMzkuNDgzTDM1OC44OTkgMzguOTkyOVpNMzU5LjU0MSA0Ny43NzdDMzYxLjYyOCA0Ny43NzcgMzYzLjM3NiA0Ni45ODUzIDM2NC43ODUgNDUuNDAxOUMzNjUuMzYzIDQ1LjY1MzMgMzY1LjY1MiA0Ni4xODExIDM2NS42NTIgNDYuOTg1M0MzNjUuNjUyIDQ4LjE5MTcgMzY0LjkzNSA0OS4zMTAyIDM2My41MDIgNTAuMzQwNkMzNjIuMDY4IDUxLjM3MTEgMzYwLjM4MyA1MS44ODYzIDM1OC40NDYgNTEuODg2M0MzNTUuNDU0IDUxLjg4NjMgMzUzLjExNSA1MS4wNjk1IDM1MS40MjkgNDkuNDM1OEMzNDkuNzY5IDQ3LjgwMjIgMzQ4LjkzOSA0NS40NjQ4IDM0OC45MzkgNDIuNDIzNkMzNDguOTM5IDM5LjM1NzQgMzQ5Ljg1NyAzNi43NDM1IDM1MS42OTMgMzQuNTgyQzM1My41MjkgMzIuNDIwNiAzNTUuODU2IDMxLjMzOTggMzU4LjY3MyAzMS4zMzk4QzM2MC44MzYgMzEuMzM5OCAzNjIuNjIyIDMyLjA0MzYgMzY0LjAzIDMzLjQ1MUMzNjUuNDM4IDM0LjgzMzQgMzY2LjE0MyAzNi40MTY4IDM2Ni4xNDMgMzguMjAxMkMzNjYuMTQzIDM5LjU4MzYgMzY1LjgxNiA0MC41NzYzIDM2NS4xNjIgNDEuMTc5NUMzNjQuNTA4IDQxLjc4MjcgMzYzLjM4OSA0Mi4xMDk1IDM2MS44MDQgNDIuMTU5N0wzNTQuODI1IDQyLjM0ODJDMzU1LjAwMSA0NS45Njc0IDM1Ni41NzMgNDcuNzc3IDM1OS41NDEgNDcuNzc3WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMzcwLjIxNiA0Ny40VjM1LjU5OTlDMzcwLjIxNiAzNC4yOTMgMzY5Ljc4OSAzMy40MTMzIDM2OC45MzQgMzIuOTYwOUMzNjkuODg5IDMxLjg4MDIgMzcxLjA0NiAzMS4zMzk4IDM3Mi40MDQgMzEuMzM5OEMzNzQuNjQzIDMxLjMzOTggMzc1Ljg3NSAzMi41NDYyIDM3Ni4xMDIgMzQuOTU5QzM3Ny4xMDggMzIuNTQ2MiAzNzguNzggMzEuMzM5OCAzODEuMTE5IDMxLjMzOThDMzgyLjMwMSAzMS4zMzk4IDM4My4yNyAzMS42OTE3IDM4NC4wMjQgMzIuMzk1NEMzODQuODA0IDMzLjA5OTIgMzg1LjE5NCAzNC4wNDE3IDM4NS4xOTQgMzUuMjIyOUMzODUuMTk0IDM2LjQwNDIgMzg0Ljg5MiAzNy4zNDY3IDM4NC4yODggMzguMDUwNEMzODMuNzEgMzguNzI5IDM4Mi44MTcgMzkuMDY4MyAzODEuNjEgMzkuMDY4M0MzODEuNDU5IDM5LjA2ODMgMzgxLjEzMiAzOS4wNDMyIDM4MC42MjkgMzguOTkyOUMzODAuNzI5IDM4LjU0MDUgMzgwLjc4IDM4LjI1MTUgMzgwLjc4IDM4LjEyNThDMzgwLjc4IDM3LjQ5NzUgMzgwLjU5MSAzNy4wMDc0IDM4MC4yMTQgMzYuNjU1NUMzNzkuODYyIDM2LjI3ODUgMzc5LjQyMiAzNi4wOSAzNzguODkzIDM2LjA5QzM3Ny41MzUgMzYuMDkgMzc2LjYxNyAzNy4xMjA1IDM3Ni4xMzkgMzkuMTgxNFY0Ny41ODg1QzM3Ni4xMzkgNDguOTQ1NyAzNzYuNTU0IDQ5LjgzOCAzNzcuMzg0IDUwLjI2NTJDMzc2LjQyOSA1MS4zNDYgMzc1LjI3MiA1MS44ODYzIDM3My45MTMgNTEuODg2M0MzNzEuNDQ5IDUxLjg4NjMgMzcwLjIxNiA1MC4zOTA5IDM3MC4yMTYgNDcuNFoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTM5NC45MjggMzUuMzczNlY0NS41NTI2QzM5NC45MjggNDcuMTM2IDM5NS41NDUgNDcuOTI3NyAzOTYuNzc3IDQ3LjkyNzdDMzk4LjEzNSA0Ny45Mjc3IDM5OS4zOTMgNDcuMzExOSA0MDAuNTUgNDYuMDgwNEM0MDAuOTAyIDQ2LjA4MDQgNDAxLjE5MSA0Ni4yMzEyIDQwMS40MTcgNDYuNTMyOEM0MDEuNjY5IDQ2LjgwOTIgNDAxLjc5NSA0Ny4xODYyIDQwMS43OTUgNDcuNjYzOEM0MDEuNzk1IDQ4LjY5NDIgNDAxLjE2NiA0OS42NjE5IDM5OS45MDggNTAuNTY2N0MzOTguNjUxIDUxLjQ0NjMgMzk3LjA5MSA1MS44ODYyIDM5NS4yMyA1MS44ODYyQzM5My4zOTQgNTEuODg2MiAzOTEuODk4IDUxLjQyMTIgMzkwLjc0MSA1MC40OTEzQzM4OS41ODQgNDkuNTYxMyAzODkuMDA1IDQ4LjE0MTMgMzg5LjAwNSA0Ni4yMzEyVjM1LjM3MzZDMzg4LjEgMzUuMzQ4NCAzODcuNDIxIDM1LjEzNDggMzg2Ljk2OCAzNC43MzI3QzM4Ni41MTUgMzQuMzA1NCAzODYuMjg5IDMzLjc3NzYgMzg2LjI4OSAzMy4xNDkzQzM4Ni4yODkgMzIuNTIwOSAzODYuNTUzIDMyLjAxODMgMzg3LjA4MSAzMS42NDEzQzM4Ny42ODUgMzEuNzE2NyAzODguMzI2IDMxLjc1NDQgMzg5LjAwNSAzMS43NTQ0VjMwLjYyMzRDMzg5LjAwNSAyOS4zMTY0IDM4OC41NzggMjguNDM2OCAzODcuNzIzIDI3Ljk4NDRDMzg4LjY3OCAyNi45MDM2IDM4OS44MSAyNi4zNjMzIDM5MS4xMTggMjYuMzYzM0MzOTIuNDUxIDI2LjM2MzMgMzkzLjQxOSAyNi43Mjc3IDM5NC4wMjMgMjcuNDU2NkMzOTQuNjI3IDI4LjE4NTQgMzk0LjkyOCAyOS4zMTY0IDM5NC45MjggMzAuODQ5NlYzMS43NTQ0SDQwMC42MjVDNDAwLjgwMSAzMS45NTU0IDQwMC44ODkgMzIuMjA2OCA0MDAuODg5IDMyLjUwODRDNDAwLjg4OSAzMy4yODc1IDQwMC40NjIgMzMuOTY2MSAzOTkuNjA2IDM0LjU0NDJDMzk4Ljc3NiAzNS4wOTcxIDM5Ny4zMTggMzUuMzczNiAzOTUuMjMgMzUuMzczNkgzOTQuOTI4WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNNDE4LjczMyAzNS41NjIyQzQxOS4wMSAzNC44MDgyIDQxOS4xNDggMzQuMTI5NiA0MTkuMTQ4IDMzLjUyNjRDNDE5LjE0OCAzMi45MjMyIDQxOS4wMSAzMi40MjA2IDQxOC43MzMgMzIuMDE4NEM0MTkuNDM4IDMxLjU2NiA0MjAuMjU1IDMxLjMzOTggNDIxLjE4NiAzMS4zMzk4QzQyMi41MTkgMzEuMzM5OCA0MjMuNTEyIDMxLjg0MjUgNDI0LjE2NiAzMi44NDc4QzQyNC40OTMgMzMuMzUwNSA0MjQuNjU2IDMzLjk0MTEgNDI0LjY1NiAzNC42MTk3QzQyNC42NTYgMzUuMjk4MyA0MjQuNDkzIDM2LjA3NzUgNDI0LjE2NiAzNi45NTcxTDQxNi45NiA1Ni40NDhDNDE1LjcwMyA1OS44NjYyIDQxMy43MTYgNjEuNTc1MiA0MTAuOTk5IDYxLjU3NTJDNDA5Ljc5MiA2MS41NzUyIDQwOC43OTkgNjEuMjYxMSA0MDguMDE5IDYwLjYzMjdDNDA3LjIzOSA2MC4wMDQ0IDQwNi44NSA1OS4yNTA0IDQwNi44NSA1OC4zNzA3QzQwNi44NSA1Ny41MTYyIDQwNy4wNzYgNTYuNzI0NSA0MDcuNTI5IDU1Ljk5NTZDNDA4LjIwOCA1Ni41NDg2IDQwOC44NjIgNTYuODI1IDQwOS40OSA1Ni44MjVDNDEwLjExOSA1Ni44MjUgNDEwLjY3MiA1Ni42MzY1IDQxMS4xNSA1Ni4yNTk1QzQxMS42NTMgNTUuOTA3NyA0MTIuMDE4IDU1LjM5MjQgNDEyLjI0NCA1NC43MTM4TDQxMy4yMjUgNTEuODQ4NkM0MTMuMTUgNTEuODQ4NiA0MTMuMDg3IDUxLjg0ODYgNDEzLjAzNyA1MS44NDg2QzQxMi4wODEgNTEuODQ4NiA0MTEuMTc1IDUxLjQ4NDIgNDEwLjMyIDUwLjc1NTNDNDA5LjQ2NSA1MC4wMDEzIDQwOC44MjQgNDguOTMzMiA0MDguMzk2IDQ3LjU1MDhMNDA0LjgxMiAzNi4wOUM0MDQuNTM2IDM1LjE4NTIgNDA0LjI4NCAzNC41MTkyIDQwNC4wNTggMzQuMDkxOUM0MDMuODU3IDMzLjYzOTUgNDAzLjU1NSAzMy4yNjI1IDQwMy4xNTIgMzIuOTYwOUM0MDQuMjg0IDMxLjg4MDIgNDA1LjQ0MSAzMS4zMzk4IDQwNi42MjMgMzEuMzM5OEM0MDcuODA1IDMxLjMzOTggNDA4LjcyMyAzMS42NzkxIDQwOS4zNzcgMzIuMzU3N0M0MTAuMDMxIDMzLjAzNjMgNDEwLjU0NyAzNC4xMTcxIDQxMC45MjQgMzUuNTk5OUw0MTQuMzk1IDQ4LjM0MjVMNDE4LjczMyAzNS41NjIyWiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMTEyLjgzNCAxOC4zMjgxSDExNC40MDZWNjAuOTI5MUgxMTIuODM0VjE4LjMyODFaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik04Mi42Mzk4IDc4LjQyMTRINTQuMjUzOVY3Mi44NTA4SDc2LjcwNjFDNzYuNzA2MSA3Mi4xMTEgNzYuNzA2MSA3MS42MDQ2IDc2LjcwNjEgNzEuMDk4MkM3Ni43MzQ4IDYxLjcwODQgNzYuNzUxMiA1Mi4zMTg1IDc2LjgwODUgNDIuOTI4N0M3Ni44MTQgNDEuOTUxMyA3Ni41NjgxIDQxLjM3NjcgNzUuNjc0OCA0MC44MDA2QzY2LjM4OSAzNC44MTM4IDU3LjE0MTUgMjguNzY2OCA0Ny45MTMxIDIyLjY4OThDNDcuMTgzNyAyMi4yMDk0IDQ2LjY5NiAyMi4xNDUyIDQ1Ljk3NzYgMjIuNjA3OUMzNi42NDY3IDI4LjYyMzUgMjcuMjk1NCAzNC42MDYzIDE3Ljk4MjMgNDAuNjQ3OEMxNy41MjA2IDQwLjk0ODEgMTcuMDk1OCA0MS43MDcgMTcuMDkzMSA0Mi4yNTU3QzE3LjAzOTggNTIuMzczMSAxNy4wNTQ4IDYyLjQ5MDUgMTcuMDU0OCA3Mi43NTk0SDQyLjQwNjlWNTguNTc1N0g1MC44MDM1Vjc4LjM3MDlIMTEuMDcwNkMxMS4wNzA2IDc3LjY1OTcgMTEuMDcwNiA3Ny4wNDU1IDExLjA3MDYgNzYuNDMxMkMxMS4wNDQ2IDYzLjkxMTUgMTEuMDA1IDUxLjM5MTcgMTEuMDM3OCAzOC44NzE5QzExLjAzOTIgMzguMzIwNCAxMS41ODQyIDM3LjU3NjUgMTIuMDg5NiAzNy4yNTAzQzIxLjcxMTQgMzEuMDE2MyAzMS4zNzU1IDI0Ljg0NTIgNDEuMDA1NSAxOC42MjQ5QzQyLjcxMDIgMTcuNTIzMyA0NC4zMTI1IDE2LjI2MzQgNDUuOTUwMiAxNS4wNjA4QzQ2LjY0MTQgMTQuNTUzMSA0Ny4yMDQyIDE0LjQzNyA0OC4wMjY1IDE0Ljk5MjZDNTkuMzg4NSAyMi42NjI1IDcwLjc4MzMgMzAuMjg2MSA4Mi4xNTYyIDM3LjkzOTZDODIuNDM3NiAzOC4xMjkzIDgyLjc0MjIgMzguNTQ0MyA4Mi43NDM2IDM4Ljg1NTVDODIuNzc2NCA1MS45NDg2IDgyLjc3NjQgNjUuMDQxNyA4Mi43NzUgNzguMTM0OEM4Mi43NzUgNzguMjI3NiA4Mi42OTE3IDc4LjMxOSA4Mi42NDI1IDc4LjQyMjhMODIuNjM5OCA3OC40MjE0WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMjMuMzM5OSA3LjU5MTA1QzI2Ljg2IDcuNTkxMDUgMzAuMjY4MSA3LjYwNjA3IDMzLjY3NjEgNy41NzE5NEMzNC4wODg2IDcuNTY3ODUgMzQuNTQ0OSA3LjM2NzE5IDM0LjkwNDEgNy4xMzY1MUMzOC41NDU3IDQuODA2NDUgNDIuMTgxOSAyLjQ2Njg0IDQ1Ljc5MzUgMC4wOTAzNzdDNDYuNTQ3NSAtMC40MDY0ODMgNDcuMTA4OSAtMC42NTIxODMgNDguMDEwNCAtMC4wNTk3NzNDNjMuMDI3NyA5LjgxODczIDc4LjA3MjQgMTkuNjUzNiA5My4xMTAyIDI5LjUwMDdDOTMuNDUzIDI5LjcyNTkgOTMuNzgzNiAyOS45NzE2IDk0LjE3MDEgMzAuMjQxOUM5My4yMTk0IDMxLjk1MzYgOTIuMjk3NCAzMy42MTYxIDkxLjI4NjYgMzUuNDM3Qzg4Ljg0MjkgMzMuODI1IDg2LjQ2NzUgMzIuMjU2NiA4NC4wODk0IDMwLjY5MjNDNzIuNTk3NiAyMy4xMzE2IDYxLjA2NDkgMTUuNjMyMyA0OS42NTEgNy45NTQxNEM0Ny42MTg0IDYuNTg3NzggNDYuMzgwOSA2LjQ1MjY0IDQ0LjIzMjIgNy44OTY4MUMzMC43ODQ0IDE2LjkzODYgMTcuMTg3NyAyNS43NTc4IDMuNjM4NzcgMzQuNjQ5NEMzLjMzODI2IDM0Ljg0NiAzLjAyODE5IDM1LjAyODkgMi41NzE5NiAzNS4zMTE1QzEuNTg4NDggMzMuNTQ1MiAwLjYzNTA0IDMxLjgzMjEgLTAuMzU5Mzc1IDMwLjA0NjdDNC4zMTIxOSAyNy4wMTY0IDguODkzNiAyNC4wNDIgMTMuNDc3NyAyMS4wNzMyQzE2LjIzMjkgMTkuMjg5MSAxOC45NzQzIDE3LjQ4NDYgMjEuNzYwOSAxNS43NDk3QzIyLjkzNTYgMTUuMDE4IDIzLjUxMDcgMTQuMTYzNSAyMy4zNzY4IDEyLjY5NzVDMjMuMjIyNSAxMC45OTgxIDIzLjM0MTMgOS4yNzI3MyAyMy4zNDEzIDcuNTkxMDVIMjMuMzM5OVoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTUxLjA4NDggNTIuMDEyMkg0Mi41NDQ4QzQyLjQ5MTUgNTEuMjIzMyA0Mi40IDUwLjQ2OTggNDIuMzk3MyA0OS43MTQ5QzQyLjM3NTQgNDMuMTA0MiA0Mi4zNDY3IDM2LjQ5MzUgNDIuMzk3MyAyOS44ODI4QzQyLjQwMTQgMjkuMjgyMiA0Mi45NDkxIDI4LjY4MyA0My4yNTc4IDI4LjA5MDZDNDMuMzAwMiAyOC4wMDg3IDQzLjQyMzEgMjcuOTY3NyA0My41MDc4IDI3LjkwNzdDNDYuOTMwOSAyNS40NzggNDYuOTQzMiAyNS40NjE2IDUwLjMyNjcgMjguMDI1MUM1MC43MDM3IDI4LjMxMDQgNTEuMDQ5MiAyOC45MTc4IDUxLjA1MiAyOS4zNzc4QzUxLjA5NyAzNi44NzAzIDUxLjA4MzQgNDQuMzYyOCA1MS4wODM0IDUyLjAxMjJINTEuMDg0OFoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTY3LjgzMDYgNTYuNzU5OUg1OS4zNTM1VjMzLjgwNDdDNjAuNzYwNCAzNC43MTEgNjIuMDA2MiAzNS43NDE2IDYzLjQyNjggMzYuMzg0NUM2Ni44NDcxIDM3LjkzMjQgNjguMjk1MSA0MC4yNTg0IDY3LjkxOTQgNDQuMTcwNUM2Ny41MjQ3IDQ4LjI4NDYgNjcuODMwNiA1Mi40NjcgNjcuODMwNiA1Ni43NTk5WiIgZmlsbD0iI0YzMDA1MSIvPgo8cGF0aCBkPSJNMzQuMjY2MSA1Ni43MjM1SDI1LjgzNjhDMjUuODM2OCA1MC45ODM3IDI1LjgzIDQ1LjMxNDggMjUuODU0NiAzOS42NDZDMjUuODU1OSAzOS4zMzA3IDI2LjAzMjEgMzguODgyOSAyNi4yNzUzIDM4LjcyMzJDMjguODc0NyAzNy4wMTgzIDMxLjUwNDIgMzUuMzU4NSAzNC4yNjQ3IDMzLjU5NzdWNTYuNzI0OEwzNC4yNjYxIDU2LjcyMzVaIiBmaWxsPSIjRjMwMDUxIi8+CjxwYXRoIGQ9Ik01OS4zNDc3IDU4LjU3ODFINjcuNjcxOFY2Ny4zNzAxSDU5LjM0NzdWNTguNTc4MVoiIGZpbGw9IiNGMzAwNTEiLz4KPHBhdGggZD0iTTI2LjAyOTMgNjcuMzc1N1Y1OC42NjAySDM0LjA1OTdWNjcuMzc1N0gyNi4wMjkzWiIgZmlsbD0iI0YzMDA1MSIvPgo8L2c+CjxkZWZzPgo8Y2xpcFBhdGggaWQ9ImNsaXAwXzExNjBfMzA3NCI+CjxyZWN0IHdpZHRoPSI0MjUiIGhlaWdodD0iNzgiIGZpbGw9IndoaXRlIi8+CjwvY2xpcFBhdGg+CjwvZGVmcz4KPC9zdmc+Cg==" data-filename="logo.f88f19f0.svg" >
            </center>
        </footer>
</div>
