
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nueva Transacción') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">  {{ __('Lista de transacciones') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('transaciones pendientes por aprobar') }}</p>
                </header>

                <div class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-bold mb-4">Monitoreo de Transacciones (Live)</h3>

                            <div class="overflow-x-auto">
                                <div class="mb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="relative w-full md:w-72">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">

                                        </div>
                                        <input type="text" id="search-reference"
                                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Buscar por referencia (TX-...)">
                                    </div>

                                    <div id="search-loader" class="hidden text-xs text-gray-500 animate-pulse">
                                        Buscando en Cashela...
                                    </div>
                                </div>
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="text-gray-400 text-sm uppercase">
                                            <th class="p-3">Referencia</th>
                                            <th class="p-3">Envía</th>
                                            <th class="p-3 text-center">Tasa</th> <th class="p-3">Recibe</th>
                                            <th class="p-3 text-center">Estado</th>
                                            <th class="p-3 text-right">Fecha</th>
                                            <th class="p-3 text-center">Acciones</th> </tr>
                                        </tr>
                                    </thead>
                                    <tbody id="transactions-table-body">
                                        <tr><td colspan="4" class="px-6 py-4 text-center">Cargando transacciones...</td></tr>
                                    </tbody>
                                </table>
                                <div class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
                                    <div class="flex justify-between flex-1 sm:hidden">
                                        <button onclick="changePage('prev')" id="btn-prev-mobile" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Anterior</button>
                                        <button onclick="changePage('next')" id="btn-next-mobile" class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Siguiente</button>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                        <p class="text-sm text-gray-700">
                                                Mostrando <span id="pagination-from" class="font-medium">0</span>
                                                a <span id="pagination-to" class="font-medium">0</span>
                                                de <span id="pagination-total" class="font-medium">0</span> resultados
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                <button onclick="changePage('prev')" id="btn-prev" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                                    <span class="sr-only">Anterior</span>
                                                    &larr;
                                                </button>
                                                <button onclick="changePage('next')" id="btn-next" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                                    <span class="sr-only">Siguiente</span>
                                                    &rarr;
                                                </button>
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchUserTransactions();
        });
         let searchTimeout = null;
        document.getElementById('search-reference').addEventListener('input', function(e) {
            const searchValue = e.target.value;
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(searchValue);
            }, 500);
        });
        async function performSearch(value) {
            const loader = document.getElementById('search-loader');
            if(loader) loader.classList.remove('hidden');
            const urlObj = new URL("{{ route('admin.operations') }}", window.location.origin);
            if (value) {
                urlObj.searchParams.append('search', value);
            }
            urlObj.searchParams.append('paginate', 10);
            urlObj.searchParams.append('order_by', 'DESC');
            await fetchUserTransactions(urlObj.toString());
            if(loader) loader.classList.add('hidden');
        }
        let nextPageUrl = null;
        let prevPageUrl = null;
        let currentFilters = {
            paginate: 10,
            order_by: 'DESC',
            sort_by: 'created_at'
        };
        async function fetchUserTransactions(targetUrl = null) {
            try {
                let finalUrl = targetUrl;
                if (!finalUrl) {
                    const urlObj = new URL("{{ route('admin.operations') }}", window.location.origin);
                    urlObj.searchParams.append('paginate', 10);
                    urlObj.searchParams.append('order_by', 'DESC');
                    finalUrl = urlObj.toString();
                }
                const response = await fetch(finalUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                console.log('Respuesta de transacciones:', response);
                if (!response.status || response.status !== 200) {
                    console.error("Error en Cashela:", response.statusText);
                    return;
                }
                const result = await response.json();
                const pagination = result.value;
                console.log('Datos de paginación:', pagination);
                nextPageUrl = pagination.next_page_url;
                prevPageUrl = pagination.prev_page_url;
                renderTable(pagination.data || []);
                updatePaginationUI(pagination);
            } catch (error) {
                console.error("Error en Cashela:", error);
            }
        }

        function updatePaginationUI(pag) {
            if (!pag || !pag.meta) {
                console.error("La estructura de paginación no es la esperada:", pag);
                return;
            }
            const from = pag.meta.from || 0;
            const to = pag.meta.to || 0;
            const total = pag.meta.total || 0;
            document.getElementById('pagination-from').innerText = from;
            document.getElementById('pagination-to').innerText = to;
            document.getElementById('pagination-total').innerText = total;
            nextPageUrl = pag.links.next;
            prevPageUrl = pag.links.prev;
            const btnNext = document.getElementById('btn-next');
            const btnPrev = document.getElementById('btn-prev');
            const btnNextMobile = document.getElementById('btn-next-mobile');
            const btnPrevMobile = document.getElementById('btn-prev-mobile');
            if (btnNext) btnNext.disabled = !nextPageUrl;
            if (btnNextMobile) btnNextMobile.disabled = !nextPageUrl;
            if (btnPrev) btnPrev.disabled = !prevPageUrl;
            if (btnPrevMobile) btnPrevMobile.disabled = !prevPageUrl;
            [btnNext, btnPrev, btnNextMobile, btnPrevMobile].forEach(btn => {
                if (btn) btn.classList.toggle('opacity-50', btn.disabled);
            });
        }
        function changePage(direction) {
            const link = (direction === 'next') ? nextPageUrl : prevPageUrl;

            if (link !== null && link !== undefined) {
                fetchUserTransactions(link);
            } else {
                console.warn("No hay más páginas en esta dirección.");
            }
        }
        function renderTable(transactions) {
            const tableBody = document.getElementById('transactions-table-body');
            if (!transactions || transactions.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-10 text-gray-500">No hay transacciones registradas.</td></tr>';
                return;
            }
            tableBody.innerHTML = '';
            transactions.forEach(tx => {
                const statusColors = {
                    'completed': 'bg-green-100 text-green-700',
                    'pending': 'bg-yellow-100 text-yellow-700',
                    'failed': 'bg-red-100 text-red-700',
                    'approved': 'bg-blue-100 text-blue-700'
                };
                const statusClass = statusColors[tx.status?.toLowerCase()] || 'bg-gray-100 text-gray-700';
                tableBody.innerHTML += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                        <td class="p-3">
                            <span class="font-mono text-xs font-bold text-blue-600">${tx.reference_code}</span>
                            <p class="text-[10px] text-gray-400 uppercase truncate w-32">${tx.description || 'Sin descripción'}</p>
                        </td>

                        <td class="p-3">
                            <div class="font-semibold text-gray-800">${tx.source?.amount || '0.00'} ${tx.source?.currency || ''}</div>
                        </td>

                        <td class="p-3 text-center">
                            <div class="inline-block bg-blue-50 border border-blue-100 px-2 py-1 rounded">
                                <span class="text-xs font-mono font-bold text-blue-700">x${tx.exchange?.rate || tx.exchange_rate}</span>
                            </div>
                        </td>

                        <td class="p-3">
                            <div class="font-semibold text-green-600">${tx.destination?.amount || '0.00'} ${tx.destination?.currency || ''}</div>
                        </td>

                        <td class="p-3 text-center">
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase ${statusClass}">
                                ${tx.status}
                            </span>
                        </td>

                        <td class="p-3 text-right text-xs text-gray-700 font-medium">
                            ${tx.created_at}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-2">
                                <button onclick="processTransaction('${tx.id}', 'completed')"
                                        class="p-1 text-green-600 hover:bg-green-50 rounded border border-green-200" title="Aceptar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </button>

                                <button onclick="processTransaction('${tx.id}', 'failed')"
                                        class="p-1 text-red-600 hover:bg-red-50 rounded border border-red-200" title="Rechazar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        document.addEventListener('DOMContentLoaded', () => fetchUserTransactions());
        /***************************************************************/
        async function processTransaction(id, state) {
            const confirmMsg = state === 'completed' ? '¿Aprobar esta transacción?' : '¿Rechazar esta transacción?';
            if (!confirm(confirmMsg)) return;

            try {
                const baseUrl = "{{ route('admin.evaluation') }}";
                const url = `${baseUrl}?id=${id}&status=${state}`;
                console.log('Base URL para evaluación:', url);
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    }
                });
                const result = await response.json();
                if (response.ok) {
                    alert('Operación realizada con éxito');
                    fetchUserTransactions(); // Recargar la tabla
                } else {
                    console.error(result);
                    alert('Error al procesar: ' + (result.message || 'Error desconocido'));
                }
            } catch (error) {
                console.error("Error en la petición:", error);
                alert('Error crítico de conexión.');
            }
        }
    </script>
</x-app-layout>
