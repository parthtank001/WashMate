class LaundryService {
  final int id;
  final String name;
  final String description;
  final double price;
  final String? iconUrl;

  LaundryService({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    this.iconUrl,
  });

  factory LaundryService.fromJson(Map<String, dynamic> json) {
    return LaundryService(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      name: json['name'] ?? '',
      description: json['description'] ?? '',
      price: json['price'] is double ? json['price'] : double.tryParse(json['price'].toString()) ?? 0.0,
      iconUrl: json['icon_url'],
    );
  }
}
