<?php
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Three.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

<div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-teal-50 via-white to-emerald-50">
    
    <!-- 3D Scene Container -->
    <div id="canvas-container" class="absolute inset-0 z-0"></div>

    <!-- Content Overlay -->
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 pointer-events-none"> <!-- content pointer events none to let orbit controls work if added, but button needs pointer-events-auto -->
        <div class="glass-panel p-8 sm:p-14 rounded-3xl text-center shadow-2xl border border-white/40 backdrop-blur-md max-w-lg w-full transform hover:scale-[1.02] transition duration-500 pointer-events-auto">
            <h1 class="text-8xl sm:text-9xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-teal-500 to-emerald-600 font-['Outfit'] leading-none">404</h1>
            <div class="h-1 w-24 bg-gradient-to-r from-teal-400 to-emerald-400 mx-auto my-6 rounded-full"></div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2 font-['Outfit']">Lost in the Network?</h2>
            <p class="text-gray-500 mb-8 max-w-xs mx-auto">The page you're looking for seems to have drifted away into the digital void.</p>
            
            <a href="<?php echo BASE_PATH; ?>/index.php" class="group relative inline-flex items-center justify-center px-8 py-4 font-bold text-white transition-all duration-200 bg-gradient-to-r from-teal-500 to-emerald-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 hover:from-teal-600 hover:to-emerald-700 shadow-lg shadow-teal-500/30">
                <span class="material-symbols-rounded mr-2 transition-transform group-hover:-translate-x-1">arrow_back</span>
                Back to Safety
            </a>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('canvas-container');
        
        // Scene Setup
        const scene = new THREE.Scene();
        // scene.background = new THREE.Color(0xf0fdfa); // Light teal-ish background
        // Transparent bg to let CSS gradient show through
        
        const camera = new THREE.PerspectiveCamera(70, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 5;

        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        // Lighting
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
        scene.add(ambientLight);

        const pointLight1 = new THREE.PointLight(0x14b8a6, 2); // Teal light
        pointLight1.position.set(2, 3, 4);
        scene.add(pointLight1);

        const pointLight2 = new THREE.PointLight(0xa78bfa, 2); // Purple hue for contrast
        pointLight2.position.set(-3, -2, 3);
        scene.add(pointLight2);

        // Main Object: Abstract Icosahedron (Network Node)
        const geometry = new THREE.IcosahedronGeometry(1.8, 0);
        const material = new THREE.MeshPhysicalMaterial({
            color: 0xffffff,
            metalness: 0.1,
            roughness: 0.1,
            transmission: 0.6, // Glass effect
            thickness: 0.5,
            clearcoat: 1.0,
            clearcoatRoughness: 0.1
        });
        const mainObj = new THREE.Mesh(geometry, material);
        scene.add(mainObj);

        // Inner core wireframe
        const wireGeo = new THREE.IcosahedronGeometry(1.2, 1);
        const wireMat = new THREE.MeshBasicMaterial({ color: 0x14b8a6, wireframe: true, transparent: true, opacity: 0.3 });
        const wireObj = new THREE.Mesh(wireGeo, wireMat);
        mainObj.add(wireObj); // Child of main obj to rotate comfortably

        // Floating Particles (Data Packets)
        const particlesGroup = new THREE.Group();
        const particleGeo = new THREE.SphereGeometry(0.05, 8, 8);
        const particleMat = new THREE.MeshBasicMaterial({ color: 0x34d399 }); // Emerald
        
        for (let i = 0; i < 40; i++) {
            const mesh = new THREE.Mesh(particleGeo, particleMat);
            const x = (Math.random() - 0.5) * 10;
            const y = (Math.random() - 0.5) * 10;
            const z = (Math.random() - 0.5) * 6; // Keep them somewhat close in Z
            mesh.position.set(x, y, z);
            particlesGroup.add(mesh);
        }
        scene.add(particlesGroup);

        // Animation Loop
        const clock = new THREE.Clock();

        function animate() {
            requestAnimationFrame(animate);

            const time = clock.getElapsedTime();

            // Rotate Main Object
            mainObj.rotation.x = time * 0.1;
            mainObj.rotation.y = time * 0.15;
            
            // Float Main Object
            mainObj.position.y = Math.sin(time * 0.8) * 0.2;

            // Rotate Core Inverse
            wireObj.rotation.x = -time * 0.2;
            wireObj.rotation.z = time * 0.1;

            // Rotate Particles Group
            particlesGroup.rotation.y = time * 0.05;

            // Mouse interaction parallax (Soft follow)
            // Optional: Add listeners for mousemove to `target` rotation
            
            renderer.render(scene, camera);
        }

        animate();

        // Handle Resize
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    });
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

