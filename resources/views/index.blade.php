<!DOCTYPE HTML>
<html class="no-js" lang="en">
    <head>
        <meta charset='utf-8'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <title> {{ settings('app_title') }}</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <link rel="icon" type="image/x-icon" href="" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap"
                rel="stylesheet">
                
            @vite(['resources/css/app.css'])
            

        <script src="{{asset('assets/js/perfect-scrollbar.min.js')}}"></script>
        <script defer src="{{asset('assets/js/popper.min.js')}}"></script>
        <script defer src="{{asset('assets/js/tippy-bundle.umd.min.js')}}"></script>
        <script defer src="{{asset('assets/js/sweetalert.min.js')}}"></script>
    </head>
    <body class="body-bg">
        <section class="bg-gray-50">
            <div class="mx-auto max-w-screen-xl px-4 py-32 lg:flex lg:h-screen lg:items-center">
            <div class="mx-auto max-w-xl text-center">
                <h1 class="text-3xl font-extrabold sm:text-5xl">
                    Custom Authentication || A Complete Laravel Custom Authentication Solution 
                </h1>
        
                <p class="mt-4 sm:text-xl/relaxed">
                    Laravel custom authentication with google 2fa. A complete auth system like register, login, verify email, forgot password etc.
                </p>
        
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                
        
                <a
                    class="block w-full rounded px-12 py-3 text-sm font-medium text-red-600 shadow hover:text-red-700 focus:outline-none focus:ring active:text-red-500 sm:w-auto"
                    href="https://github.com/itech-eng/custom-authentication"
                >
                    Learn More
                </a>
                </div>
            </div>
            </div>
        </section>

        <script src="{{asset('assets/js/alpine-collaspe.min.js')}}"></script>
    <script src="{{asset('assets/js/alpine-persist.min.js')}}"></script>
    <script defer src="{{asset('assets/js/alpine-ui.min.js')}}"></script>
    <script defer src="{{asset('assets/js/alpine-focus.min.js')}}"></script>
    <script defer src="{{asset('assets/js/alpine.min.js')}}"></script>
    </body>
</html>