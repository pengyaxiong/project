<canvas id="Simple"></canvas>

<script>
    $(function () {
        var config = {

            type: 'doughnut',

            data: {
                datasets: [{

                    data: [123, 123, 212, 123, 123, 123, 123, 123, 123, 123],

                    backgroundColor: ['rgb(54, 162, 235)', 'rgb(255, 99, 132)', 'rgb(255, 205, 86)']

                }],

                labels: {!!  $staffs !!}

            },

            options: {
                maintainAspectRatio: true,
                animation: {
                    animateRotate: true,
                    animateScale: true,
                },
            }

        };

        var ctx = document.getElementById('Simple').getContext('2d');

        new Chart(ctx, config);
    });
</script>