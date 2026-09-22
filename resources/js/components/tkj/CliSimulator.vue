<script setup>
import { ref, nextTick } from 'vue';

const mode = ref('cisco'); // 'cisco' | 'mikrotik'
const hostname = ref('Router');
const promptMode = ref('user'); // user | privileged | config
const riwayat = ref([
    { tipe: 'info', teks: 'Simulator CLI Cisco/MikroTik dasar. Ketik "help" untuk daftar perintah.' },
]);
const input = ref('');
const historyList = ref([]);
const historyIndex = ref(-1);
const terminalRef = ref(null);

const promptText = () => {
    if (mode.value === 'mikrotik') return `[admin@${hostname.value}] > `;
    if (promptMode.value === 'user') return `${hostname.value}>`;
    if (promptMode.value === 'privileged') return `${hostname.value}#`;
    return `${hostname.value}(config)#`;
};

function tulis(teks, tipe = 'output') {
    riwayat.value.push({ tipe, teks });
}

async function scrollBawah() {
    await nextTick();
    terminalRef.value?.scrollTo(0, terminalRef.value.scrollHeight);
}

const perintahCisco = {
    help: () => `Perintah tersedia:\n enable, configure terminal, hostname <nama>, interface <nama>\n ip address <ip> <mask>, no shutdown, shutdown\n vlan <id>, name <nama-vlan>\n ip dhcp pool <nama>, network <net> <mask>, default-router <ip>\n show ip interface brief, show running-config, show vlan brief\n show mac address-table, show arp, show ip route\n write memory, ping <ip>, exit, clear, help`,
    enable: () => { promptMode.value = 'privileged'; return ''; },
    'configure terminal': () => { promptMode.value = 'config'; return 'Enter configuration commands, one per line.'; },
    exit: () => {
        if (promptMode.value === 'config') promptMode.value = 'privileged';
        else if (promptMode.value === 'privileged') promptMode.value = 'user';
        return '';
    },
    'show ip interface brief': () => 'Interface              IP-Address      Status    Protocol\nGigabitEthernet0/0     unassigned      down      down',
    'show running-config': () => `hostname ${hostname.value}\n!\ninterface GigabitEthernet0/0\n no ip address\n!\nend`,
    'show vlan brief': () => 'VLAN Name                             Status    Ports\n---- -------------------------------- --------- -------------------------\n1    default                          active    Gi0/1, Gi0/2, Gi0/3\n10   Siswa                            active\n20   Guru                             active',
    'show mac address-table': () => '          Mac Address Table\n-------------------------------------------\nVlan    Mac Address       Type        Ports\n----    -----------       --------    -----\n   1    aabb.cc00.0100     DYNAMIC     Gi0/1\n   1    aabb.cc00.0200     DYNAMIC     Gi0/2',
    'show arp': () => 'Protocol  Address          Age (min)  Hardware Addr   Type   Interface\nInternet  192.168.1.1             -   aabb.cc00.0100  ARPA   GigabitEthernet0/0',
    'show ip route': () => 'Codes: C - connected, S - static, R - RIP, O - OSPF\n\nC    192.168.1.0/24 is directly connected, GigabitEthernet0/0\nS*   0.0.0.0/0 [1/0] via 192.168.1.1',
    'write memory': () => 'Building configuration...\n[OK]',
    wr: () => 'Building configuration...\n[OK]',
};

function jalankanCisco(cmd) {
    if (cmd.startsWith('hostname ')) {
        hostname.value = cmd.slice(9).trim() || hostname.value;
        return '';
    }
    if (cmd.startsWith('interface ')) {
        return `Masuk ke konfigurasi interface ${cmd.slice(10).trim()}.`;
    }
    if (cmd.startsWith('ip address ')) {
        const [ip, mask] = cmd.slice(11).trim().split(/\s+/);
        return ip && mask ? `IP address ${ip} ${mask} diterapkan pada interface.` : 'Format: ip address <ip> <subnet-mask>';
    }
    if (cmd === 'no shutdown' || cmd === 'no shut') {
        return '%LINK-3-UPDOWN: Interface diaktifkan (up).';
    }
    if (cmd === 'shutdown') {
        return '%LINK-5-CHANGED: Interface dinonaktifkan (administratively down).';
    }
    if (cmd.startsWith('vlan ')) {
        return `VLAN ${cmd.slice(5).trim()} dibuat/dipilih untuk dikonfigurasi.`;
    }
    if (cmd.startsWith('name ')) {
        return `Nama VLAN diatur menjadi "${cmd.slice(5).trim()}".`;
    }
    if (cmd.startsWith('ip dhcp pool ')) {
        return `DHCP pool "${cmd.slice(13).trim()}" dibuat.`;
    }
    if (cmd.startsWith('network ')) {
        const [net, mask] = cmd.slice(8).trim().split(/\s+/);
        return net && mask ? `Jaringan DHCP diatur ke ${net} ${mask}.` : 'Format: network <network-address> <subnet-mask>';
    }
    if (cmd.startsWith('default-router ')) {
        return `Default gateway DHCP diatur ke ${cmd.slice(15).trim()}.`;
    }
    if (cmd.startsWith('ping ')) {
        const target = cmd.slice(5).trim();
        return `Sending 5, 100-byte ICMP Echos to ${target}, timeout is 2 seconds:\n!!!!!\nSuccess rate is 100 percent (5/5)`;
    }
    if (perintahCisco[cmd]) return perintahCisco[cmd]();

    return `% Perintah tidak dikenali: "${cmd}"`;
}

const perintahMikrotik = {
    help: () => 'Perintah tersedia:\n /ip address add address=<ip/prefix> interface=<if>, /ip address print\n /ip pool add name=<nama> ranges=<range>\n /ip dhcp-server add address-pool=<pool> interface=<if> name=<nama>\n /ip firewall nat add chain=srcnat action=masquerade out-interface=<if>\n /ip firewall filter add chain=input action=accept connection-state=established,related\n /ip route print, /ip firewall nat print, /interface print\n /system identity set name=<nama>, /system resource print\n /ping <ip>, clear, help',
    '/ip address print': () => 'Flags: X - disabled, I - invalid, D - dynamic\n #   ADDRESS            NETWORK         INTERFACE\n 0   192.168.88.1/24    192.168.88.0    ether1',
    '/interface print': () => 'Flags: R - running\n #     NAME       TYPE       ACTUAL-MTU\n 0  R ether1     ether      1500\n 1  R ether2     ether      1500',
    '/ip route print': () => 'Flags: X - disabled, A - active, D - dynamic, C - connect, S - static\n #      DST-ADDRESS        GATEWAY         DISTANCE\n 0 ADS  0.0.0.0/0          192.168.88.1    1\n 1 ADC  192.168.88.0/24    ether1          0',
    '/ip firewall nat print': () => 'Flags: X - disabled, I - invalid, D - dynamic\n 0   chain=srcnat action=masquerade out-interface=ether1',
    '/system resource print': () => '     uptime: 1h2m3s\n    version: 7.15 (stable)\n   cpu-load: 3%\n free-memory: 512.0MiB\ntotal-memory: 1024.0MiB',
};

function jalankanMikrotik(cmd) {
    if (cmd.startsWith('/system identity set name=')) {
        hostname.value = cmd.split('name=')[1]?.trim() || hostname.value;
        return 'Identity berhasil diubah.';
    }
    if (cmd.startsWith('/ip address add')) {
        return 'Alamat IP berhasil ditambahkan.';
    }
    if (cmd.startsWith('/ip pool add')) {
        return 'Pool alamat IP berhasil dibuat.';
    }
    if (cmd.startsWith('/ip dhcp-server add')) {
        return 'DHCP server berhasil dibuat. Jangan lupa buat DHCP network di /ip dhcp-server network.';
    }
    if (cmd.startsWith('/ip firewall nat add')) {
        return 'Aturan NAT berhasil ditambahkan.';
    }
    if (cmd.startsWith('/ip firewall filter add')) {
        return 'Aturan firewall filter berhasil ditambahkan.';
    }
    if (cmd.startsWith('/ping ')) {
        const target = cmd.slice(6).trim();
        return `  SEQ HOST                                     SIZE TTL TIME\n    0 ${target}                                56  64 1ms\n    sent=1 received=1 packet-loss=0%`;
    }
    if (perintahMikrotik[cmd]) return perintahMikrotik[cmd]();

    return `bad command name ${cmd.split(' ')[0]} (line 1 column 1)`;
}

function eksekusi() {
    const cmd = input.value.trim();
    if (! cmd) return;

    tulis(`${promptText()} ${cmd}`, 'command');
    historyList.value.push(cmd);
    historyIndex.value = historyList.value.length;

    if (cmd === 'clear') {
        riwayat.value = [];
    } else {
        const hasil = mode.value === 'cisco' ? jalankanCisco(cmd) : jalankanMikrotik(cmd);
        if (hasil) tulis(hasil);
    }

    input.value = '';
    scrollBawah();
}

function historyAtas() {
    if (historyIndex.value > 0) {
        historyIndex.value -= 1;
        input.value = historyList.value[historyIndex.value];
    }
}

function historyBawah() {
    if (historyIndex.value < historyList.value.length - 1) {
        historyIndex.value += 1;
        input.value = historyList.value[historyIndex.value];
    } else {
        historyIndex.value = historyList.value.length;
        input.value = '';
    }
}

function gantiMode(m) {
    mode.value = m;
    promptMode.value = 'user';
    hostname.value = m === 'cisco' ? 'Router' : 'MikroTik';
    riwayat.value = [{ tipe: 'info', teks: `Mode ${m === 'cisco' ? 'Cisco IOS' : 'MikroTik RouterOS'} aktif. Ketik "help" untuk daftar perintah.` }];
}
</script>

<template>
    <div class="rounded-xl bg-white dark:bg-slate-800 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-primary-900 dark:text-white">CLI Simulator</h2>
            <div class="flex gap-1.5">
                <button
                    class="rounded-lg px-3 py-1.5 text-xs font-medium"
                    :class="mode === 'cisco' ? 'bg-primary-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'"
                    @click="gantiMode('cisco')"
                >
                    Cisco IOS
                </button>
                <button
                    class="rounded-lg px-3 py-1.5 text-xs font-medium"
                    :class="mode === 'mikrotik' ? 'bg-primary-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'"
                    @click="gantiMode('mikrotik')"
                >
                    MikroTik
                </button>
            </div>
        </div>

        <div
            ref="terminalRef"
            class="h-80 overflow-y-auto rounded-lg bg-slate-900 p-4 font-mono text-sm text-green-400"
            @click="$refs.inputRef?.focus()"
        >
            <div v-for="(baris, i) in riwayat" :key="i" class="whitespace-pre-wrap break-all">
                <span v-if="baris.tipe === 'command'" class="text-white">{{ baris.teks }}</span>
                <span v-else-if="baris.tipe === 'info'" class="text-slate-400">{{ baris.teks }}</span>
                <span v-else>{{ baris.teks }}</span>
            </div>
            <div class="flex items-center gap-1">
                <span class="text-white shrink-0">{{ promptText() }}</span>
                <input
                    ref="inputRef"
                    v-model="input"
                    type="text"
                    autocomplete="off"
                    spellcheck="false"
                    class="flex-1 bg-transparent border-none text-green-400 focus:outline-none focus:ring-0 p-0"
                    @keydown.enter="eksekusi"
                    @keydown.up.prevent="historyAtas"
                    @keydown.down.prevent="historyBawah"
                />
            </div>
        </div>
    </div>
</template>
