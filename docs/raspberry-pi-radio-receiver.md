# DIY Radio Receiver with Raspberry Pi 3 and 3.5″ Touch Display

A beginner-friendly guide to building a software-defined radio (SDR) receiver on a
Raspberry Pi 3, using a 3.5″ SPI TFT touch display for the UI and headphone output
from the Pi's built-in 3.5 mm analog jack.

> **Receive-only disclaimer:** This guide covers *receive-only* radio operation, which
> is legal for personal use in India without a license. Transmitting on any radio
> frequency requires a valid license from the Wireless Planning & Coordination (WPC)
> Wing of the Ministry of Communications. Do **not** transmit with the hardware
> described here.

---

## Table of Contents

1. [Bill of Materials](#1-bill-of-materials)
2. [Connection Overview](#2-connection-overview)
3. [Raspberry Pi OS Setup](#3-raspberry-pi-os-setup)
4. [Enabling the 3.5″ SPI TFT Display](#4-enabling-the-35-spi-tft-display)
5. [Routing Audio to the 3.5 mm Jack](#5-routing-audio-to-the-35-mm-jack)
6. [Software Options](#6-software-options)
   - [Option A – Internet Radio (VLC / MPD)](#option-a--internet-radio-vlc--mpd)
   - [Option B – SDR FM Receiver (RTL-SDR)](#option-b--sdr-fm-receiver-rtl-sdr)
7. [Troubleshooting](#7-troubleshooting)
8. [Further Reading](#8-further-reading)

---

## 1. Bill of Materials

| Item | Notes | Approx. India Cost |
|------|-------|--------------------|
| **Raspberry Pi 3 Model B / B+** | with 40-pin GPIO header | ₹3,000–₹4,000 |
| **microSD card** (16 GB or larger, Class 10) | For Raspberry Pi OS | ₹250–₹500 |
| **5 V / 2.5 A micro-USB power supply** | Official Pi PSU recommended | ₹500–₹800 |
| **3.5″ SPI TFT touch display** | 320×480, ILI9486/ILI9488 + XPT2046 or ADS7846 touch; plugs directly onto GPIO header | ₹600–₹1,200 |
| **RTL-SDR USB dongle** (RTL2832U + R820T2) | For FM/VHF/UHF reception | ₹1,500–₹2,500 |
| **Telescopic / FM dipole antenna** | Often included with dongle kit | — |
| **3.5 mm stereo headphones** | Any standard headphones | ₹200–₹2,000 |
| **USB DAC (optional)** | e.g., small USB sound card; reduces audio noise compared with Pi's PWM jack | ₹200–₹800 |

> **Note on shortwave (HF, 0–30 MHz):** The standard RTL-SDR dongle covers roughly
> 24 MHz–1.7 GHz. It *can* receive HF in "direct sampling" mode, but sensitivity is
> limited. For serious shortwave listening, consider an **HF-capable SDR** (e.g.,
> Airspy HF+) or an **RTL-SDR + upconverter**.

---

## 2. Connection Overview

```
  ┌─────────────────────────────────────────────────────┐
  │                  Raspberry Pi 3                      │
  │                                                      │
  │  [40-pin GPIO header]──────► 3.5" SPI TFT display   │
  │       SPI0 (MOSI/MISO/SCLK/CE0)                     │
  │       GPIO for DC, RST, touch CS/IRQ                 │
  │       3.3 V + 5 V power pins, GND                   │
  │                                                      │
  │  [USB port] ───────────────► RTL-SDR dongle          │
  │                                  │                   │
  │                             [Antenna]                │
  │                                                      │
  │  [3.5 mm audio jack] ──────► Headphones              │
  │      (or optional USB DAC → headphones)              │
  └─────────────────────────────────────────────────────┘
```

**GPIO pin usage for a typical 3.5″ SPI TFT display (header view):**

Most 3.5″ SPI TFT modules designed for the Pi plug directly onto the 40-pin header and
use these signals:

| Signal | GPIO (BCM) | Physical Pin |
|--------|-----------|--------------|
| SPI MOSI | GPIO 10 | Pin 19 |
| SPI MISO | GPIO 9  | Pin 21 |
| SPI SCLK | GPIO 11 | Pin 23 |
| LCD Chip Select (CE0) | GPIO 8 | Pin 24 |
| LCD DC / Register Select | GPIO 25 | Pin 22 |
| LCD Reset | GPIO 27 | Pin 13 |
| Touch CS | GPIO 7  | Pin 26 |
| Touch IRQ | GPIO 17 | Pin 11 |
| 3.3 V | — | Pin 1 / 17 |
| 5 V    | — | Pin 2 / 4  |
| GND    | — | Pin 6 / 9 / 14 / 20 / 25 |

> Exact pins vary by module brand. Always cross-check with your display's datasheet or
> product page before connecting.

---

## 3. Raspberry Pi OS Setup

### 3.1 Flash the OS

1. Download **Raspberry Pi Imager** from <https://www.raspberrypi.com/software/>.
2. Flash **Raspberry Pi OS (32-bit, Bullseye or later)** to the microSD card.
3. Before first boot you can use Imager's *Advanced settings* (gear icon) to:
   - Set hostname, username/password.
   - Enable SSH (useful for headless initial setup).
   - Configure Wi-Fi (needed for internet radio option).

### 3.2 First Boot and Updates

```bash
sudo apt update && sudo apt full-upgrade -y
sudo reboot
```

### 3.3 Enable SPI

The SPI bus is required by the 3.5″ TFT display.

```bash
sudo raspi-config
```

Navigate to **Interface Options → SPI → Yes → Finish**.

Or enable it non-interactively:

```bash
sudo raspi-config nonint do_spi 0
```

Verify SPI is enabled:

```bash
lsmod | grep spi
# Should show: spi_bcm2835
```

---

## 4. Enabling the 3.5″ SPI TFT Display

### 4.1 Identify Your Display Controller

Before installing any driver, identify your display:

1. Look at the **PCB silkscreen** on the back of the display for chip markings such as:
   - `ILI9486`, `ILI9488`, `ST7796` (display controller)
   - `XPT2046`, `ADS7846` (touch controller)
2. Check your product listing/invoice for the brand name:
   - **Waveshare** (e.g., "Waveshare 3.5inch RPi LCD (A)" or "(B)")
   - **LCD-show / MPI3501** style (many generic Chinese modules)

### 4.2 Waveshare 3.5″ LCD

Waveshare provides their own driver package. Visit the product page on
<https://www.waveshare.com> for the exact model and follow their wiki. The general
steps are:

```bash
cd ~
git clone https://github.com/waveshare/LCD-show.git
cd LCD-show
chmod +x LCD35-show
./LCD35-show   # or LCD35B-show depending on model variant
# The script modifies /boot/config.txt and reboots automatically.
```

After reboot the desktop should appear on the TFT display.

### 4.3 Generic LCD-show / MPI3501 Style Display

Many generic 3.5″ modules are supported by the community `LCD-show` fork:

```bash
cd ~
git clone https://github.com/goodtft/LCD-show.git
cd LCD-show
chmod +x LCD35-show
sudo ./LCD35-show
# Script modifies /boot/config.txt and /etc/X11/xorg.conf, then reboots.
```

### 4.4 Manual config.txt Overlay (ILI9486 example)

If you prefer not to use a third-party install script, add the following to
`/boot/config.txt` (for ILI9486-based displays — verify against your module's
documentation before applying):

```ini
# Enable SPI
dtparam=spi=on

# ILI9486 display overlay
dtoverlay=piscreen,speed=16000000,rotate=90

# Force audio to 3.5mm jack (see Section 5)
dtparam=audio=on
```

Then reboot:

```bash
sudo reboot
```

> **Safe approach:** Use the installer script provided by your display brand/seller
> rather than manually editing config.txt. Incorrect overlays can prevent the Pi from
> booting to the display. If the screen goes blank after a change, connect an HDMI
> monitor, log in via SSH, and revert the config.txt edit.

### 4.5 Screen Rotation

If the display content is rotated incorrectly, add or change the `rotate=` parameter in
the overlay line in `/boot/config.txt` (values: `0`, `90`, `180`, `270`):

```ini
dtoverlay=piscreen,speed=16000000,rotate=270
```

### 4.6 Touch Calibration

Install the calibration tool:

```bash
sudo apt install -y xinput-calibrator
```

Run calibration:

```bash
DISPLAY=:0 xinput_calibrator
```

Follow the on-screen prompts (tap the four corner crosses). Copy the output
`<calibration>` section into `/etc/X11/xorg.conf.d/99-calibration.conf`.

---

## 5. Routing Audio to the 3.5 mm Jack

### 5.1 Via raspi-config

```bash
sudo raspi-config
```

Navigate to **System Options → Audio → Headphones**.

### 5.2 Via amixer (command line)

```bash
# Force 3.5mm analog jack
amixer cset numid=3 1
```

| Value | Output |
|-------|--------|
| `0` | Auto (follows HDMI if connected) |
| `1` | 3.5 mm analog jack |
| `2` | HDMI |

Make it permanent by adding the command to `/etc/rc.local` (before `exit 0`) or a
systemd unit.

### 5.3 Set Volume

```bash
alsamixer
```

- Press `F6` to select the correct sound card (`bcm2835 Headphones`).
- Use arrow keys to raise volume; press `M` to unmute.

### 5.4 Audio Quality Note

The Pi 3's 3.5 mm jack is PWM-based and can have audible hiss, especially at low volume.
To improve quality:

- Use a **USB DAC** (small USB sound card). Set it as the default ALSA device:
  ```bash
  # Find card number:
  aplay -l
  # Edit ~/.asoundrc:
  echo 'defaults.pcm.card 1
  defaults.ctl.card 1' >> ~/.asoundrc
  ```
- Keep power supply and antenna cables away from the Pi to reduce ground-loop noise.
- Use a quality 5 V PSU (cheap PSUs introduce significant audio noise).

---

## 6. Software Options

### Option A – Internet Radio (VLC / MPD)

Internet radio streams are usually CD-quality or better and bypass all RF reception
issues (needs a working internet connection).

#### Install VLC

```bash
sudo apt install -y vlc
```

#### Play a station from the command line (headless/SSH)

```bash
# Example: All India Radio (replace URL with any .m3u / .pls stream URL)
cvlc --alsa-audio-device default http://air.pc.cdn.bitgravity.com/air/live/pbaudio001/playlist.m3u
```

Press `Ctrl+C` to stop.

#### Install MPD + MPC (background daemon + CLI control)

```bash
sudo apt install -y mpd mpc
```

Edit `/etc/mpd.conf` — locate the `audio_output` block and set it to the ALSA headphone
device:

```
audio_output {
    type        "alsa"
    name        "Headphones"
    device      "hw:0,0"
    mixer_type  "hardware"
}
```

Add a station playlist:

```bash
mkdir -p ~/music/playlists
# Create a playlist file with your favourite stream URL:
echo "http://your-stream-url/stream.mp3" > ~/music/playlists/mystation.m3u
sudo systemctl restart mpd
mpc load mystation
mpc play
mpc volume 80
```

#### Touch-friendly full-screen UI

For a simple touch interface on the 3.5″ screen:

- **Mopidy** with the **Iris** web extension provides a touch-friendly browser UI. Run it
  as a service and open Chromium in kiosk mode pointing at `http://localhost:6680/iris`.
- Alternatively, **`ncmpcpp`** runs in the terminal and is keyboard/touch navigable.

Launch Chromium in full-screen kiosk mode (add to autostart):

```bash
chromium-browser --kiosk --noerrdialogs --disable-infobars http://localhost:6680/iris &
```

---

### Option B – SDR FM Receiver (RTL-SDR)

#### Install RTL-SDR drivers

```bash
sudo apt install -y rtl-sdr
# Blacklist the kernel DVB driver that conflicts with rtl-sdr:
echo 'blacklist dvb_usb_rtl28xxu' | sudo tee /etc/modprobe.d/blacklist-rtlsdr.conf
sudo modprobe -r dvb_usb_rtl28xxu 2>/dev/null || true
```

Plug in the RTL-SDR dongle and verify it is detected:

```bash
rtl_test -t
# Expected output: Found 1 device(s): ...
```

#### Receive FM Radio (command line, headphone jack output)

```bash
# Tune to 91.1 MHz FM (change frequency to a strong local station)
rtl_fm -f 91.1M -M wbfm -s 200000 -r 48000 - | aplay -r 48000 -f S16_LE -t raw -c 1
```

Key options:

| Flag | Meaning |
|------|---------|
| `-f 91.1M` | Tune frequency (change to your local FM station) |
| `-M wbfm` | Wide-band FM demodulation |
| `-s 200000` | Sample rate from dongle (200 kHz) |
| `-r 48000` | Output sample rate to audio device |

To adjust volume use `alsamixer` in another terminal.

#### Stop the stream

Press `Ctrl+C`.

#### Scan for active FM stations

```bash
# Sweep 87.5–108 MHz in 100 kHz steps, stop at first signal above threshold
rtl_power -f 87.5M:108M:100k -g 40 -i 1 scan.csv
```

#### Notes on HF / Shortwave

- **Direct sampling mode:** RTL-SDR can be put into Q-branch direct sampling to receive
  HF, but sensitivity is significantly lower than purpose-built HF receivers.
  ```bash
  rtl_fm -f 7.2M -M am -s 250000 -r 48000 -E direct - | aplay -r 48000 -f S16_LE -t raw -c 1
  ```
- For better shortwave performance, use an **HF upconverter** (e.g., Ham-It-Up) or an
  HF-capable SDR device.
- A long-wire antenna (10–20 m) improves HF reception considerably.

#### GUI option: GQRX

GQRX is a graphical SDR receiver. On Pi 3 it may be slow but is usable for exploring
the spectrum:

```bash
sudo apt install -y gqrx-sdr
gqrx &
```

Set the audio output device to `Default` (ALSA → default) in the GQRX audio settings.

---

## 7. Troubleshooting

### No audio from headphones

1. Confirm the jack is selected:
   ```bash
   amixer cset numid=3 1
   ```
2. Raise volume and unmute in `alsamixer`.
3. Check that another application is not blocking the ALSA device.
4. If using a USB DAC, confirm `~/.asoundrc` points to the correct card number (`aplay -l`).

### Audio goes to HDMI instead of headphones

Run after every boot (or add to `/etc/rc.local`):

```bash
amixer cset numid=3 1
```

Or disable HDMI audio in `/boot/config.txt`:

```ini
# Disable HDMI audio
hdmi_drive=1
```

### Noisy / hissy audio

- Switch to a USB DAC.
- Use a better quality 5 V power supply.
- Move the antenna cable away from the Pi and power cables.
- Add ferrite chokes to USB and power cables.
- Try shielding the Pi with a metal enclosure (leave ventilation).

### RTL-SDR dongle not detected

```bash
lsusb | grep RTL
# Should show: Bus 00x Device 00x: ID 0bda:2838 Realtek ...
```

If missing, try a different USB port or a powered USB hub (Pi 3 USB ports share a
single USB 2.0 controller and limited current budget).

Ensure the DVB driver is blacklisted:

```bash
cat /etc/modprobe.d/blacklist-rtlsdr.conf
# Should contain: blacklist dvb_usb_rtl28xxu
sudo reboot
```

### Touchscreen not responding or wrongly calibrated

1. Run `xinput_calibrator` and apply the output config.
2. If touch events are mirrored, add `Option "SwapXY" "1"` or `"InvertX"/"InvertY"` in
   the calibration config file.
3. Check which input device is registered:
   ```bash
   DISPLAY=:0 xinput list
   ```

### Touchscreen rotation does not match display rotation

Rotation is separate for display and touch. Add a `TransformationMatrix` to the input
device in Xorg config, or use `xinput set-prop`:

```bash
# Example: 90-degree clockwise rotation
DISPLAY=:0 xinput set-prop "ADS7846 Touchscreen" "Coordinate Transformation Matrix" \
    0 1 0 -1 0 1 0 0 1
```

### Display stays blank / Pi does not boot to TFT after config change

1. Connect an HDMI monitor to see any boot errors.
2. Or SSH in and check the boot log:
   ```bash
   journalctl -b | grep -i spi
   ```
3. Revert the last `/boot/config.txt` change:
   ```bash
   sudo nano /boot/config.txt
   ```

### Power / Ground loops causing audio buzz

- Use the same power supply for the Pi and any other powered device in the chain.
- Use isolating USB hub if adding powered USB devices.
- A USB DAC often reduces ground-loop noise compared with the Pi's onboard jack.

---

## 8. Further Reading

- [Raspberry Pi Documentation – Configuration](https://www.raspberrypi.com/documentation/computers/configuration.html)
- [RTL-SDR Quick Start Guide](https://www.rtl-sdr.com/rtl-sdr-quick-start-guide/)
- [GQRX SDR Receiver](https://gqrx.dk/)
- [Waveshare 3.5″ LCD Wiki](https://www.waveshare.com/wiki/3.5inch_RPi_LCD_(A))
- [goodtft/LCD-show (generic SPI displays)](https://github.com/goodtft/LCD-show)
- [OpenWebRX (web-based SDR UI)](https://www.openwebrx.de/)
- [MPD Music Player Daemon](https://www.musicpd.org/doc/html/)
