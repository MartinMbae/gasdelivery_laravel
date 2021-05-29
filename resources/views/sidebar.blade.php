<div class="sidebar" data-color="purple" data-background-color="white" data-image="../assets/img/sidebar-1.jpg">
    <!--
      Tip 1: You can change the color of the sidebar using: data-color="purple | azure | green | orange | danger"

      Tip 2: you can also add an image using data-image tag
  -->
    <div class="logo"><a href="{{ url('/') }}" class="simple-text logo-normal">
            {{ env('APP_NAME') }}
        </a></div>
    <div class="sidebar-wrapper">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link " href="{{ url('/') }}">
                    <i class="material-icons">dashboard</i>
                    <p>Dashboard</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="{{ url('gas') }}">
                    <i class="material-icons">list</i>
                    <p>Gas Cookers</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="{{ url('orders') }}">
                    <i class="material-icons">list</i>
                    <p>Orders</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="{{ url('companies') }}">
                    <i class="material-icons">building</i>
                    <p>Gas Companies</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="{{ url('users') }}">
                    <i class="material-icons">people</i>
                    <p>Registered Users</p>
                </a>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="{{ url('payments') }}">
                    <i class="material-icons">money</i>
                    <p>Payments</p>
                </a>
            </li>
        </ul>
    </div>
</div>
