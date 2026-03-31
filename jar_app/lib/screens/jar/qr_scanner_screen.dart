import 'package:flutter/material.dart';
import 'package:jar_app/core/app_colors.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class QRScannerScreen extends StatefulWidget {
  const QRScannerScreen({super.key});

  @override
  State<QRScannerScreen> createState() => _QRScannerScreenState();
}

class _QRScannerScreenState extends State<QRScannerScreen> {
  final MobileScannerController cameraController = MobileScannerController();
  bool _isScanned = false;

  @override
  void dispose() {
    cameraController.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (_isScanned) return;

    final List<Barcode> barcodes = capture.barcodes;
    for (final barcode in barcodes) {
      if (barcode.rawValue != null) {
        setState(() => _isScanned = true);
        cameraController.stop();
        Navigator.pop(context, barcode.rawValue);
        break; // Stop after first successful scan
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: const Text('Scan QR Code Lisensi', style: TextStyle(color: Colors.white)),
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: Stack(
        alignment: Alignment.center,
        children: [
          MobileScanner(
            controller: cameraController,
            onDetect: _onDetect,
          ),
          // Scanner Overlay overlay
          Container(
            decoration: ShapeDecoration(
              shape: QrScannerOverlayShape(
                borderColor: AppColors.goldIslamic,
                borderRadius: 20,
                borderLength: 40,
                borderWidth: 10,
                cutOutSize: MediaQuery.of(context).size.width * 0.7,
              ),
            ),
          ),
          Positioned(
            bottom: 60,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              decoration: BoxDecoration(
                color: Colors.black54,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Text(
                'Arahkan kamera ke QR Code di Jar Anda',
                style: TextStyle(color: Colors.white),
              ),
            ),
          )
        ],
      ),
    );
  }
}

class QrScannerOverlayShape extends ShapeBorder {
  final Color borderColor;
  final double borderWidth;
  final double overlayColor;
  final double borderRadius;
  final double borderLength;
  final double cutOutSize;

  const QrScannerOverlayShape({
    this.borderColor = Colors.red,
    this.borderWidth = 3.0,
    this.overlayColor = 150, // 0-255 opacity
    this.borderRadius = 0,
    this.borderLength = 40,
    required this.cutOutSize,
  });

  @override
  EdgeInsetsGeometry get dimensions => const EdgeInsets.all(10);

  @override
  Path getInnerPath(Rect rect, {TextDirection? textDirection}) {
    return Path()
      ..fillType = PathFillType.evenOdd
      ..addPath(getOuterPath(rect, textDirection: textDirection), Offset.zero);
  }

  @override
  Path getOuterPath(Rect rect, {TextDirection? textDirection}) {
    Path path = Path()..addRect(rect);
    final width = cutOutSize;
    final height = cutOutSize;
    path.addRRect(
      RRect.fromRectAndRadius(
        Rect.fromCenter(
          center: rect.center,
          width: width,
          height: height,
        ),
        Radius.circular(borderRadius),
      ),
    );
    return path;
  }

  @override
  void paint(Canvas canvas, Rect rect, {TextDirection? textDirection}) {
    final width = cutOutSize;
    final height = cutOutSize;
    final rrect = RRect.fromRectAndRadius(
      Rect.fromCenter(
        center: rect.center,
        width: width,
        height: height,
      ),
      Radius.circular(borderRadius),
    );

    final backgroundPaint = Paint()
      ..color = Colors.black.withAlpha(overlayColor.toInt())
      ..style = PaintingStyle.fill;
    
    canvas.drawPath(
      Path.combine(PathOperation.difference, Path()..addRect(rect), Path()..addRRect(rrect)),
      backgroundPaint,
    );

    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = borderWidth;

    final path = Path();
    final left = rect.center.dx - width / 2;
    final right = rect.center.dx + width / 2;
    final top = rect.center.dy - height / 2;
    final bottom = rect.center.dy + height / 2;

    // Top left corner
    path.moveTo(left, top + borderLength);
    path.quadraticBezierTo(left, top, left + borderLength, top);
    
    // Top right corner
    path.moveTo(right - borderLength, top);
    path.quadraticBezierTo(right, top, right, top + borderLength);

    // Bottom right corner
    path.moveTo(right, bottom - borderLength);
    path.quadraticBezierTo(right, bottom, right - borderLength, bottom);

    // Bottom left corner
    path.moveTo(left + borderLength, bottom);
    path.quadraticBezierTo(left, bottom, left, bottom - borderLength);

    canvas.drawPath(path, borderPaint);
  }

    @override
  ShapeBorder scale(double t) {
    return QrScannerOverlayShape(
      borderColor: borderColor,
      borderWidth: borderWidth,
      overlayColor: overlayColor,
      cutOutSize: cutOutSize,
    );
  }
}
