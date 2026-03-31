<html>
{{--@include("includes.head")--}}
<body>
@yield('content')

<style>
    .skeleton-form {
        border: 1px solid gray;
        margin: 0.5rem;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // document.getElementById('menu-button').addEventListener('click', function () {
        //     toggleSidebar();
        // });
        // document.getElementById('sidebar-bg').addEventListener('click', function () {
        //     toggleSidebar();
        // });

        document.querySelectorAll('input, select, textarea').forEach(function (element) {
            element.classList.add('skeleton-form');
        });

    })
</script>

</body>

</html>

