<nav class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <a href="{{ route('home') }}" class="text-lg font-semibold text-gray-900">PsyCare</a>

        <div class="flex items-center gap-4">
            <a
                href="{{ route('medical-center.login') }}"
                target="_blank"
                rel="noopener"
                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Clinic Portal
            </a>

            @auth('web')
                <span class="text-sm text-gray-600">{{ auth('web')->user()->name }}</span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
                    Login
                </a>

                <a
                    href="{{ route('register') }}"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                >
                    Signup
                </a>
            @endauth
        </div>
    </div>
</nav>
