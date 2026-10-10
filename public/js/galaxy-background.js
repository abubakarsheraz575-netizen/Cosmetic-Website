(function() {
    if (typeof window.Galaxy === 'function') return;

    window.Galaxy = function(canvas, options) {
        var context = canvas && canvas.getContext('2d');
        if (!context) return { stop: function() {} };

        options = options || {};
        var tau = Math.PI * 2;
        var count = Math.min(options.count || 220, 320);
        var points = [];
        var lines = [];
        var width;
        var height;
        var centerX;
        var centerY;
        var radius;
        var rotation = Math.random() * tau;
        var frameId;
        var stopped = false;
        var mouse = { x: 0, y: 0 };

        for (var i = 0; i < count; i++) {
            var y = 1 - (i / (count - 1)) * 2;
            var radial = Math.sqrt(Math.max(0, 1 - y * y));
            var angle = Math.PI * (3 - Math.sqrt(5)) * i;
            points.push({
                x: Math.cos(angle) * radial,
                y: y,
                z: Math.sin(angle) * radial,
                size: 0.5 + Math.random(),
                color: Math.random() < 0.82 ? '56,170,180' : '125,219,226'
            });
        }

        for (var pointIndex = 0; pointIndex < count; pointIndex++) {
            for (var otherIndex = pointIndex + 1; otherIndex < count; otherIndex++) {
                var dx = points[pointIndex].x - points[otherIndex].x;
                var dy = points[pointIndex].y - points[otherIndex].y;
                var dz = points[pointIndex].z - points[otherIndex].z;
                if (dx * dx + dy * dy + dz * dz < 0.045) {
                    lines.push([pointIndex, otherIndex]);
                }
            }
        }

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            centerX = width / 2;
            centerY = height / 2;
            radius = Math.min(width, height) * (options.radius || 0.44);
        }

        function moveMouse(event) {
            mouse.x = event.clientX / window.innerWidth - 0.5;
            mouse.y = event.clientY / window.innerHeight - 0.5;
        }

        function draw(time) {
            if (stopped) return;
            rotation += 0.00008;
            context.clearRect(0, 0, width, height);

            var sine = Math.sin(rotation + mouse.x * 0.25);
            var cosine = Math.cos(rotation + mouse.x * 0.25);
            var tilt = -0.32 + mouse.y * 0.3;
            var tiltSine = Math.sin(tilt);
            var tiltCosine = Math.cos(tilt);
            var projected = new Array(points.length);
            var pulse = 1 + Math.sin(time * 0.001) * 0.08;
            var glow = context.createRadialGradient(centerX, centerY, 0, centerX, centerY, radius * 1.3);
            glow.addColorStop(0, 'rgba(125,219,226,0.13)');
            glow.addColorStop(1, 'rgba(56,170,180,0)');
            context.fillStyle = glow;
            context.fillRect(0, 0, width, height);

            for (var p = 0; p < points.length; p++) {
                var point = points[p];
                var rotatedX = point.x * cosine - point.z * sine;
                var rotatedZ = point.x * sine + point.z * cosine;
                var rotatedY = point.y * tiltCosine - rotatedZ * tiltSine;
                var depth = point.y * tiltSine + rotatedZ * tiltCosine;
                var scale = 2.6 / (2.6 + depth);
                projected[p] = {
                    x: centerX + rotatedX * radius * scale,
                    y: centerY + rotatedY * radius * scale,
                    alpha: Math.max(0.06, (scale - 0.68) * 1.1),
                    scale: scale
                };
            }

            context.lineWidth = 0.55;
            for (var lineIndex = 0; lineIndex < lines.length; lineIndex++) {
                var pair = lines[lineIndex];
                var first = projected[pair[0]];
                var second = projected[pair[1]];
                var alpha = (first.alpha + second.alpha) * 0.12;
                if (alpha < 0.025) continue;
                context.strokeStyle = 'rgba(43,127,184,' + alpha.toFixed(3) + ')';
                context.beginPath();
                context.moveTo(first.x, first.y);
                context.lineTo(second.x, second.y);
                context.stroke();
            }

            for (var starIndex = 0; starIndex < points.length; starIndex++) {
                var star = points[starIndex];
                var position = projected[starIndex];
                context.fillStyle = 'rgba(' + star.color + ',' + (position.alpha * pulse).toFixed(3) + ')';
                context.beginPath();
                context.arc(position.x, position.y, star.size * position.scale, 0, tau);
                context.fill();
            }

            frameId = window.requestAnimationFrame(draw);
        }

        resize();
        window.addEventListener('resize', resize);
        window.addEventListener('mousemove', moveMouse);
        frameId = window.requestAnimationFrame(draw);

        var stop = function() {
            if (stopped) return;
            stopped = true;
            window.cancelAnimationFrame(frameId);
            window.removeEventListener('resize', resize);
            window.removeEventListener('mousemove', moveMouse);
            window.removeEventListener('ajax:navigating', stop);
            window.removeEventListener('pagehide', stop);
        };
        window.addEventListener('ajax:navigating', stop, { once: true });
        window.addEventListener('pagehide', stop, { once: true });

        return {
            stop: stop
        };
    };
})();
